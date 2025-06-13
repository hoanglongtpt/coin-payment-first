<?php

use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\PaypalController;
use App\Http\Controllers\Web\TelegramController;
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


Route::get('/', [PaymentController::class, 'index'])->name('web.index');
Route::get('/video/{telegram_id}', [PaymentController::class, 'index'])->name('web.index.video');
Route::get('/photo/{telegram_id}', [PaymentController::class, 'index'])->name('web.index.photo');
Route::get('/check', [PaymentController::class, 'check_telegram_id'])->name('web.check_telegram_id');
Route::post('/spin', [PaymentController::class, 'spin'])->name('web.spin');

Route::get('paypal/checkout', [PaypalController::class, 'checkout'])->name('paypal.checkout');
Route::get('paypal/payment-success', [PayPalController::class, 'successTransaction'])->name('successTransaction');
Route::get('paypal/payment-cancel', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');

// vip

Route::get('paypal/checkout-vip', [PaypalController::class, 'checkout_vip'])->name('paypal.checkout.vip');
Route::get('paypal/payment-success-vip', [PayPalController::class, 'successTransaction_vip'])->name('successTransaction.vip');
Route::get('paypal/payment-cancel-vip', [PayPalController::class, 'cancelTransaction_vip'])->name('cancelTransaction.vip');



