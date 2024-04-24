<?php

use App\Http\Controllers\BkashDynamicChargeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dynamic Charging (User)
Route::get('/bkash-pay', [BkashDynamicChargeController::class, 'payment'])->name('bkash-pay');
Route::post('/bkash-create', [BkashDynamicChargeController::class, 'createPayment'])->name('bkash-create');
Route::get('/bkash-dynamic-callback', [BkashDynamicChargeController::class, 'callback'])->name('bkash-dynamic-callback');

// Dynamic Charging (Admin)
Route::get('/bkash-refund', [BkashDynamicChargeController::class, 'getRefund'])->name('bkash-get-refund');
Route::post('/bkash-refund', [BkashDynamicChargeController::class, 'refundPayment'])->name('bkash-post-refund');
Route::get('/bkash-search', [BkashDynamicChargeController::class, 'getSearchTransaction'])->name('bkash-get-search');
Route::post('/bkash-search', [BkashDynamicChargeController::class, 'searchTransaction'])->name('bkash-post-search');