<?php

use App\Http\Controllers\AdminDropController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DropController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DropController::class, 'current'])->name('home');

Route::get('drops/{drop:slug}', [DropController::class, 'show'])->name('drops.show');
Route::get('drops/{drop:slug}/state', [DropController::class, 'stateJson'])->name('drops.state');
Route::post('drops/{drop:slug}/waitlist', [DropController::class, 'joinWaitlist'])
    ->middleware('throttle:10,1')
    ->name('drops.waitlist');
Route::post('drops/{drop:slug}/reserve', [ReservationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('drops.reserve');
Route::get('reservations/{reservation:token}', [ReservationController::class, 'show'])->name('reservations.show');

Route::post('reservations/{reservation:token}/pay', [CheckoutController::class, 'start'])
    ->middleware('throttle:10,1')
    ->name('checkout.start');
Route::get('reservations/{reservation:token}/simulate', [CheckoutController::class, 'simulate'])->name('checkout.simulate');
Route::post('reservations/{reservation:token}/simulate', [CheckoutController::class, 'confirm'])->name('checkout.confirm');

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

Route::get('archives', [ArchiveController::class, 'index'])->name('archives');

Route::prefix('admin')->middleware('auth.basic.drop')->name('admin.')->group(function () {
    Route::get('drops/create', [AdminDropController::class, 'create'])->name('drops.create');
    Route::post('drops', [AdminDropController::class, 'store'])->name('drops.store');
});
