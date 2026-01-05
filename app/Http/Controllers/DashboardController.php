<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get stats for the last 30 days
        $stats = Invoice::select(
            DB::raw('DATE(date) as date'),
            DB::raw('count(*) as count'),
            DB::raw('sum(total) as total_amount')
        )
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->limit(30)
        ->get();

        return response()->json([
            'stats' => $stats
        ]);
    }
}
