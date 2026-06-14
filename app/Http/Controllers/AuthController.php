<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, ActivityLogger $logger): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The email address or password is incorrect.'],
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['This account is inactive. Please contact an administrator.'],
            ]);
        }

        $logger->log('login', $user, 'User logged in.');

        return response()->json(['user' => $user]);
    }

    public function logout(Request $request, ActivityLogger $logger): JsonResponse
    {
        $logger->log('logout', $request->user(), 'User logged out.');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        return response()->json([
            'message' => 'Email confirmed. You can now set a new password.',
        ]);
    }

    public function resetPassword(Request $request, ActivityLogger $logger): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();
        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        $logger->log('password_reset', $user, 'User reset their password.');

        return response()->json([
            'message' => 'Password reset successfully. You can now sign in.',
        ]);
    }
}
