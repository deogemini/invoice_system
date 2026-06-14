<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'clients' => User::where('role', User::ROLE_CLIENT)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $client = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_CLIENT,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $logger->log('client.created', $client, 'Client account created.');

        return response()->json([
            'message' => 'Client created successfully.',
            'client' => $client,
        ], 201);
    }

    public function show(User $client): JsonResponse
    {
        abort_unless($client->isClient(), 404);

        return response()->json(['client' => $client]);
    }

    public function update(Request $request, User $client, ActivityLogger $logger): JsonResponse
    {
        abort_unless($client->isClient(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($client->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        $client->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $data['is_active'],
        ]);

        if (!empty($data['password'])) {
            $client->password = $data['password'];
        }

        $client->save();
        $logger->log('client.updated', $client, 'Client account updated.');

        return response()->json([
            'message' => 'Client updated successfully.',
            'client' => $client,
        ]);
    }
}
