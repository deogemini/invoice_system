<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with('customer')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'invoices' => $invoices
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'reference' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $sub_total = 0;
            $items_data = [];

            foreach ($request->items as $item) {
                $line_total = $item['unit_price'] * $item['quantity'];
                $sub_total += $line_total;
                $items_data[] = [
                    'product_id' => $item['product_id'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                ];
            }

            $discount = $request->input('discount', 0);
            $total = $sub_total - $discount;

            $invoice = Invoice::create([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'due_date' => $request->due_date,
                'reference' => $request->reference,
                'terms_and_conditions' => $request->terms_and_conditions,
                'sub_total' => $sub_total,
                'discount' => $discount,
                'total' => $total,
            ]);

            // Generate Invoice Number
            $invoice->number = 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT);
            $invoice->save();

            foreach ($items_data as $item_data) {
                $invoice->items()->create($item_data);
            }

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load('items', 'customer')
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
        $invoice = Invoice::with(['customer', 'items.product'])->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }
        return response()->json([
            'invoice' => $invoice
        ]);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $request->validate([
            'status' => 'sometimes|in:paid,unpaid',
            'tra_status' => 'sometimes|in:generated,not_generated'
        ]);

        if ($request->has('status')) {
            $invoice->status = $request->status;
        }

        if ($request->has('tra_status')) {
            $invoice->tra_status = $request->tra_status;
        }

        $invoice->save();

        return response()->json(['message' => 'Invoice updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
