<?php

use App\Http\Controllers\ApiTokenManagerController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\OrderCompleteController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomepageController::class, 'index'])->name('homepage');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/{order}', [CheckoutController::class, 'show'])->middleware('signed')->name('checkout.show');
Route::get('/order-complete/{order}', OrderCompleteController::class)->middleware('signed')->name('orders.complete');

Route::middleware('auth')->group(function () {
    Route::get('/product-images', [ProductImageController::class, 'index'])->middleware('verified')->name('product-images.index');
    Route::put('/product-images/{product}', [ProductImageController::class, 'update'])->middleware('verified')->name('product-images.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('password.confirm')->name('profile.edit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/api-tokens', [ApiTokenManagerController::class, 'index'])->middleware(['verified', 'password.confirm'])->name('apiTokens.index');
});
