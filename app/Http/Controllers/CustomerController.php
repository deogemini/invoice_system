<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->withCount('invoices')
            ->visibleTo(request()->user())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'customers' => $customers
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ActivityLogger $logger)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'address' => 'nullable|string',
            'tin' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'p_o_box' => 'nullable|string|max:50',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        $validatedData['user_id'] = $request->user()->isAdministrator()
            ? ($validatedData['user_id'] ?? $request->user()->id)
            : $request->user()->id;

        $customer = Customer::create($validatedData);
        $logger->log('customer.created', $customer, 'Customer created.');

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        return response()->json(['customer' => $customer], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, ActivityLogger $logger)
    {
        $customer = Customer::visibleTo($request->user())->find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:customers,email,' . $id,
            'address' => 'nullable|string',
            'tin' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'p_o_box' => 'nullable|string|max:50',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        if (!$request->user()->isAdministrator()) {
            unset($validatedData['user_id']);
        }

        $customer->update($validatedData);
        $logger->log('customer.updated', $customer, 'Customer updated.');

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, ActivityLogger $logger)
    {
        $customer = Customer::visibleTo($request->user())->find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->forceFill(['deleted_by' => $request->user()->id])->save();
        $logger->log('customer.deleted', $customer, 'Customer deleted.');
        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully'], 200);
    }
}
