<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json([
            'products' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ActivityLogger $logger)
    {
        $validatedData = $request->validate([
            'item_code' => 'required|string|unique:products',
            'description' => 'required|string',
            'unit_price' => 'required|numeric',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        $validatedData['user_id'] = $request->user()->isAdministrator()
            ? ($validatedData['user_id'] ?? $request->user()->id)
            : $request->user()->id;

        $product = Product::create($validatedData);
        $logger->log('product.created', $product, 'Product created.');

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['owner:id,name,email,role', 'creator:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['product' => $product], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, ActivityLogger $logger)
    {
        $product = Product::visibleTo($request->user())->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validatedData = $request->validate([
            'item_code' => 'sometimes|required|string|unique:products,item_code,' . $id,
            'description' => 'sometimes|required|string',
            'unit_price' => 'sometimes|required|numeric',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);

        if (!$request->user()->isAdministrator()) {
            unset($validatedData['user_id']);
        }

        $product->update($validatedData);
        $logger->log('product.updated', $product, 'Product updated.');

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, ActivityLogger $logger)
    {
        $product = Product::visibleTo($request->user())->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->forceFill(['deleted_by' => $request->user()->id])->save();
        $logger->log('product.deleted', $product, 'Product deleted.');
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
