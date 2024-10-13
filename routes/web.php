<?php

use App\Http\Controllers\CashfreePaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;


// FIRST WAY- NOT WORKING
Route::get('/', [PaymentController::class, 'index']);
// Route::post("/pay", [PaymentController::class, "initialPayment"])->name("payment.pay");
// Route::get('/success/{orderId}', [PaymentController::class, 'PaymentSuccess'])->name('payment.success');


Route::get('/cashfree/payments/create', [CashfreePaymentController::class,'create'])->name('callback');
Route::post('/cashfree/payments/store', [CashfreePaymentController::class,'store'])->name('store');
Route::get('/cashfree/payments/success', [CashfreePaymentController::class,'success'])->name('success');