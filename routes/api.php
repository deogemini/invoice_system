<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CompanySettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('company-settings', [CompanySettingController::class, 'show']);
Route::post('company-settings', [CompanySettingController::class, 'update']);

Route::apiResource('customers', CustomerController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('invoices', InvoiceController::class);
Route::apiResource('bankaccounts', BankAccountController::class);
