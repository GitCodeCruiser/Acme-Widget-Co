<?php

use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('cart')->group(function () {
    Route::get('/total', [CartController::class, 'total']);
    Route::post('/add/{productCode}', [CartController::class, 'add']);
    Route::delete('/items/{productId}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);
});
