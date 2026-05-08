<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;


Route::get('/', [CartController::class, 'index'])->name('products.index');
Route::prefix('cart')->group(function () {
    Route::get('/total', [CartController::class, 'total'])->name('cart.total');
    Route::post('/add/{productCode}', [CartController::class, 'add'])->name('cart.add');
});
 