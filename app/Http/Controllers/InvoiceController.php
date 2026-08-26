<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with(['customer', 'owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'invoices' => $invoices
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ActivityLogger $logger)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'reference' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'include_vat' => 'nullable|boolean',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
            'document_type' => 'required|in:invoice,quotation',
        ]);

        $ownerId = $request->user()->isAdministrator()
            ? ($request->input('user_id') ?? $request->user()->id)
            : $request->user()->id;

        abort_unless(Customer::visibleTo($request->user())->whereKey($request->customer_id)->exists(), 403);
        abort_unless(!$request->filled('bank_account_id') || BankAccount::visibleTo($request->user())->whereKey($request->bank_account_id)->exists(), 403);
        abort_unless(Product::visibleTo($request->user())->whereIn('id', collect($request->items)->pluck('product_id'))->count() === count(array_unique(collect($request->items)->pluck('product_id')->all())), 403);

        try {
            DB::beginTransaction();

            $sub_total = 0;
            $items_data = [];

            foreach ($request->items as $item) {
                $line_total = $item['unit_price'] * $item['quantity'];
                $sub_total += $line_total;
                $items_data[] = [
                    'user_id' => $ownerId,
                    'product_id' => $item['product_id'],
                    'description' => $item['description'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                ];
            }

            $discount = min((float) $request->input('discount', 0), $sub_total);
            $taxableAmount = max(0, $sub_total - $discount);
            $includeVat = $request->boolean('include_vat');
            $vatRate = 18;
            $vatAmount = $includeVat ? round($taxableAmount * ($vatRate / 100), 2) : 0;
            $total = $taxableAmount + $vatAmount;

            $invoice = Invoice::create([
                'user_id' => $ownerId,
                'document_type' => $request->document_type,
                'customer_id' => $request->customer_id,
                'bank_account_id' => $request->document_type === 'quotation' ? null : ($request->input('bank_account_id') ?: null),
                'date' => $request->date,
                'due_date' => $request->due_date,
                'reference' => $request->reference,
                'terms_and_conditions' => $request->terms_and_conditions,
                'sub_total' => $sub_total,
                'discount' => $discount,
                'include_vat' => $includeVat,
                'vat_rate' => $vatRate,
                'vat_amount' => $vatAmount,
                'total' => $total,
            ]);

            // Generate Invoice Number
            $prefix = $request->document_type === 'quotation' ? 'QUO-' : 'INV-';
            $invoice->number = $prefix . str_pad($invoice->id, 5, '0', STR_PAD_LEFT);
            $invoice->save();

            foreach ($items_data as $item_data) {
                $invoice->items()->create($item_data);
            }

            $logger->log('invoice.created', $invoice, 'Invoice created.');

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('items', 'customer', 'bankAccount')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create invoice', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['customer', 'bankAccount', 'items.product', 'owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        return response()->json([
            'invoice' => $invoice
        ]);
    }

    public function update(Request $request, $id, ActivityLogger $logger)
    {
        $invoice = Invoice::visibleTo($request->user())->findOrFail($id);

        if ($request->has('items')) {
            $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'date' => 'required|date',
                'due_date' => 'nullable|date',
                'reference' => 'nullable|string',
                'discount' => 'nullable|numeric|min:0',
                'include_vat' => 'nullable|boolean',
                'terms_and_conditions' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.description' => 'nullable|string',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
                'document_type' => 'sometimes|in:invoice,quotation',
            ]);

            $ownerId = $request->user()->isAdministrator()
                ? ($request->input('user_id') ?? $invoice->user_id ?? $request->user()->id)
                : $invoice->user_id;

            abort_unless(Customer::visibleTo($request->user())->whereKey($request->customer_id)->exists(), 403);
            abort_unless(!$request->filled('bank_account_id') || BankAccount::visibleTo($request->user())->whereKey($request->bank_account_id)->exists(), 403);
            abort_unless(Product::visibleTo($request->user())->whereIn('id', collect($request->items)->pluck('product_id'))->count() === count(array_unique(collect($request->items)->pluck('product_id')->all())), 403);

            try {
                DB::beginTransaction();

                $sub_total = 0;
                $items_data = [];

                foreach ($request->items as $item) {
                    $line_total = $item['unit_price'] * $item['quantity'];
                    $sub_total += $line_total;
                    $items_data[] = [
                        'user_id' => $ownerId,
                        'product_id' => $item['product_id'],
                        'description' => $item['description'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                    ];
                }

                $discount = min((float) $request->input('discount', 0), $sub_total);
                $taxableAmount = max(0, $sub_total - $discount);
                $includeVat = $request->boolean('include_vat');
                $vatRate = 18;
                $vatAmount = $includeVat ? round($taxableAmount * ($vatRate / 100), 2) : 0;
                $total = $taxableAmount + $vatAmount;

                $invoice->update([
                    'document_type' => $request->input('document_type', $invoice->document_type),
                    'user_id' => $ownerId,
                    'customer_id' => $request->customer_id,
                    'bank_account_id' => $request->input('document_type', $invoice->document_type) === 'quotation' ? null : ($request->input('bank_account_id') ?: null),
                    'date' => $request->date,
                    'due_date' => $request->due_date,
                    'reference' => $request->reference,
                    'terms_and_conditions' => $request->terms_and_conditions,
                    'sub_total' => $sub_total,
                    'discount' => $discount,
                    'include_vat' => $includeVat,
                    'vat_rate' => $vatRate,
                    'vat_amount' => $vatAmount,
                    'total' => $total,
                ]);

                $invoice->items()->delete();

                foreach ($items_data as $item_data) {
                    $invoice->items()->create($item_data);
                }

                $payableTotal = max(0, $invoice->total);

                if (($invoice->paid_amount ?? 0) > $payableTotal) {
                    $invoice->paid_amount = $payableTotal;
                }

                if (($invoice->paid_amount ?? 0) >= $payableTotal && $payableTotal > 0) {
                    $invoice->status = 'paid';
                } elseif (($invoice->paid_amount ?? 0) > 0) {
                    $invoice->status = 'partial';
                } else {
                    $invoice->status = 'unpaid';
                }

                $invoice->save();
                $logger->log('invoice.updated', $invoice, 'Invoice updated.');

                DB::commit();

                return response()->json([
                    'message' => 'Invoice updated successfully',
                    'invoice' => $invoice->load('items', 'customer', 'bankAccount')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Failed to update invoice', 'error' => $e->getMessage()], 500);
            }
        }

        $request->validate([
            'status' => 'sometimes|in:paid,unpaid,partial',
            'tra_status' => 'sometimes|in:generated,not_generated'
        ]);

        if ($request->has('status')) {
            $invoice->status = $request->status;

            // Keep the amount-based totals consistent with the manually selected status.
            if ($request->status === 'paid') {
                $invoice->paid_amount = $invoice->total;
            } elseif ($request->status === 'unpaid') {
                $invoice->paid_amount = 0;
            }
        }

        if ($request->has('tra_status')) {
            $invoice->tra_status = $request->tra_status;
        }

        $invoice->save();
        $logger->log('invoice.status_updated', $invoice, 'Invoice status updated.');

        return response()->json([
            'message' => 'Invoice updated successfully',
            'invoice' => $invoice->fresh(),
        ]);
    }

    /**
     * Record a payment against the invoice (partial or full).
     */
    public function recordPayment(Request $request, $id, ActivityLogger $logger)
    {
        $invoice = Invoice::visibleTo($request->user())->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $amount = $request->input('amount');

        $invoice->paid_amount = ($invoice->paid_amount ?? 0) + $amount;

        if ($invoice->paid_amount >= $invoice->total) {
            $invoice->paid_amount = $invoice->total;
            $invoice->status = 'paid';
        } elseif ($invoice->paid_amount > 0) {
            $invoice->status = 'partial';
        }

        $invoice->save();
        $logger->log('invoice.payment_recorded', $invoice, 'Payment recorded.', ['amount' => $amount]);

        return response()->json(['message' => 'Payment recorded', 'invoice' => $invoice]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, ActivityLogger $logger)
    {
        $invoice = Invoice::visibleTo($request->user())->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $invoice->forceFill(['deleted_by' => $request->user()->id])->save();
        $logger->log('invoice.deleted', $invoice, 'Invoice deleted.');
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
