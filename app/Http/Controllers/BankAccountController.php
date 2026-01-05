<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::orderBy('created_at', 'desc')->get();
        return response()->json([
            'bank_accounts' => $bankAccounts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'swift_code' => 'nullable|string',
            'currency' => 'required|string',
        ]);

        $bankAccount = BankAccount::create($request->all());

        return response()->json([
            'message' => 'Bank account created successfully',
            'bank_account' => $bankAccount
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bankAccount = BankAccount::find($id);
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }
        return response()->json([
            'bank_account' => $bankAccount
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bankAccount = BankAccount::find($id);
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        $request->validate([
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'swift_code' => 'nullable|string',
            'currency' => 'required|string',
        ]);

        $bankAccount->update($request->all());

        return response()->json([
            'message' => 'Bank account updated successfully',
            'bank_account' => $bankAccount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bankAccount = BankAccount::find($id);
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        $bankAccount->delete();

        return response()->json(['message' => 'Bank account deleted successfully']);
    }
}
