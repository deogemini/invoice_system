<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WithholdingTaxReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        if (($filters['from'] ?? null) && ($filters['to'] ?? null) && $filters['to'] < $filters['from']) {
            throw ValidationException::withMessages([
                'to' => 'The to date must be on or after the from date.',
            ]);
        }

        $query = Invoice::visibleTo($request->user())
            ->where('document_type', 'invoice')
            ->where('discount', '>', 0)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('date', '<=', $date));

        $summary = (clone $query)
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(discount), 0) as total_withholding')
            ->first();

        $invoices = (clone $query)
            ->with('customer:id,name')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'number', 'date', 'customer_id', 'sub_total', 'discount', 'total', 'status']);

        $vatQuery = Invoice::visibleTo($request->user())
            ->where('document_type', 'invoice')
            ->where('include_vat', true)
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('date', '<=', $date));

        $vatSummary = (clone $vatQuery)
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(vat_amount), 0) as total_vat')
            ->first();

        $vatInvoices = (clone $vatQuery)
            ->with('customer:id,name')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'number', 'date', 'customer_id', 'sub_total', 'discount', 'vat_rate', 'vat_amount', 'total']);

        return response()->json([
            'summary' => [
                'invoice_count' => (int) $summary->invoice_count,
                'total_withholding' => (float) $summary->total_withholding,
            ],
            'invoices' => $invoices,
            'vat_summary' => [
                'invoice_count' => (int) $vatSummary->invoice_count,
                'total_vat' => (float) $vatSummary->total_vat,
            ],
            'vat_invoices' => $vatInvoices,
        ]);
    }
}
