<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeliveryNoteController extends Controller
{
    public function index()
    {
        $deliveryNotes = DeliveryNote::with(['customer', 'owner:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo(request()->user())
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['delivery_notes' => $deliveryNotes]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $this->validatePayload($request);
        $ownerId = $request->user()->isAdministrator()
            ? ($request->input('user_id') ?? $request->user()->id)
            : $request->user()->id;

        abort_unless(Customer::visibleTo($request->user())->whereKey($data['customer_id'])->exists(), 403);

        return DB::transaction(function () use ($data, $ownerId, $logger) {
            $deliveryNote = DeliveryNote::create([
                'user_id' => $ownerId,
                'customer_id' => $data['customer_id'],
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            $deliveryNote->number = 'DN-' . str_pad($deliveryNote->id, 5, '0', STR_PAD_LEFT);
            $deliveryNote->save();

            foreach ($data['items'] as $item) {
                $deliveryNote->items()->create([
                    'user_id' => $ownerId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'supplier_signature' => $item['supplier_signature'] ?? null,
                ]);
            }

            $logger->log('delivery_note.created', $deliveryNote, 'Delivery note created.');

            return response()->json([
                'message' => 'Delivery note created successfully',
                'delivery_note' => $deliveryNote->load('customer', 'items'),
            ], 201);
        });
    }

    public function show(Request $request, string $id)
    {
        $deliveryNote = DeliveryNote::with(['customer', 'items', 'owner:id,name,email,role', 'updater:id,name,email,role'])
            ->visibleTo($request->user())
            ->find($id);

        if (!$deliveryNote) {
            return response()->json(['message' => 'Delivery note not found'], 404);
        }

        return response()->json(['delivery_note' => $deliveryNote]);
    }

    public function update(Request $request, string $id, ActivityLogger $logger)
    {
        $deliveryNote = DeliveryNote::visibleTo($request->user())->findOrFail($id);
        $data = $this->validatePayload($request);
        $ownerId = $request->user()->isAdministrator()
            ? ($request->input('user_id') ?? $deliveryNote->user_id ?? $request->user()->id)
            : $deliveryNote->user_id;

        abort_unless(Customer::visibleTo($request->user())->whereKey($data['customer_id'])->exists(), 403);

        return DB::transaction(function () use ($deliveryNote, $data, $ownerId, $logger) {
            $deliveryNote->update([
                'user_id' => $ownerId,
                'customer_id' => $data['customer_id'],
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'terms_and_conditions' => $data['terms_and_conditions'] ?? null,
            ]);

            $deliveryNote->items()->delete();

            foreach ($data['items'] as $item) {
                $deliveryNote->items()->create([
                    'user_id' => $ownerId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'supplier_signature' => $item['supplier_signature'] ?? null,
                ]);
            }

            $logger->log('delivery_note.updated', $deliveryNote, 'Delivery note updated.');

            return response()->json([
                'message' => 'Delivery note updated successfully',
                'delivery_note' => $deliveryNote->load('customer', 'items'),
            ]);
        });
    }

    public function destroy(Request $request, string $id, ActivityLogger $logger)
    {
        $deliveryNote = DeliveryNote::visibleTo($request->user())->find($id);

        if (!$deliveryNote) {
            return response()->json(['message' => 'Delivery note not found'], 404);
        }

        $deliveryNote->forceFill(['deleted_by' => $request->user()->id])->save();
        $logger->log('delivery_note.deleted', $deliveryNote, 'Delivery note deleted.');
        $deliveryNote->delete();

        return response()->json(['message' => 'Delivery note deleted successfully']);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'reference' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.supplier_signature' => 'nullable|string',
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
        ]);
    }
}
