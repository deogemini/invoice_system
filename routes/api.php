<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('company-settings', [CompanySettingController::class, 'show']);
    Route::post('company-settings', [CompanySettingController::class, 'update']);
    Route::get('dashboard/stats', [DashboardController::class, 'index']);

    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
    Route::apiResource('bankaccounts', BankAccountController::class);

    Route::middleware('role:administrator')->prefix('admin')->group(function () {
        Route::apiResource('clients', ClientController::class)->except(['destroy']);
    });
});
