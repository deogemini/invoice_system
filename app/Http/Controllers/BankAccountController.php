<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAccounts = BankAccount::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'bank_accounts' => $bankAccounts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'swift_code' => 'nullable|string',
            'currency' => 'required|string',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        $data['user_id'] = $request->user()->isAdministrator()
            ? ($data['user_id'] ?? $request->user()->id)
            : $request->user()->id;

        $bankAccount = BankAccount::create($data);
        $logger->log('bank_account.created', $bankAccount, 'Bank account created.');

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
        $bankAccount = BankAccount::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->find($id);
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
    public function update(Request $request, string $id, ActivityLogger $logger)
    {
        $bankAccount = BankAccount::visibleTo($request->user())->find($id);
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        $data = $request->validate([
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
            'swift_code' => 'nullable|string',
            'currency' => 'required|string',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        if (!$request->user()->isAdministrator()) {
            unset($data['user_id']);
        }

        $bankAccount->update($data);
        $logger->log('bank_account.updated', $bankAccount, 'Bank account updated.');

        return response()->json([
            'message' => 'Bank account updated successfully',
            'bank_account' => $bankAccount
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, ActivityLogger $logger)
    {
        $bankAccount = BankAccount::visibleTo($request->user())->find($id);
        if (!$bankAccount) {
            return response()->json(['message' => 'Bank account not found'], 404);
        }

        $bankAccount->forceFill(['deleted_by' => $request->user()->id])->save();
        $logger->log('bank_account.deleted', $bankAccount, 'Bank account deleted.');
        $bankAccount->delete();

        return response()->json(['message' => 'Bank account deleted successfully']);
    }
}
