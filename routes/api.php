<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Order API routes
Route::apiResource('orders', OrderController::class);
Route::get('orders-statistics', [OrderController::class, 'statistics']);
Route::patch('orders/{order}/mark-as-paid', [OrderController::class, 'markAsPaid']);
