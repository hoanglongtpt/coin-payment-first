<?php

use App\Http\Controllers\Web\PaymentController;
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
Route::get('/check', [PaymentController::class, 'check_telegram_id'])->name('web.check_telegram_id');
Route::post('/spin', [PaymentController::class, 'spin'])->name('web.spin');

