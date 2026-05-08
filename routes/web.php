<?php

use App\Http\Controllers\CartController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::get('/products', function () {
    $products = Product::orderBy('name')->get();

    return view('products.index', [
        'products' => $products,
    ]);
})->name('products.index');

Route::prefix('cart')->group(function () {
    Route::get('/total', [CartController::class, 'total']);
    Route::post('/add/{productCode}', [CartController::class, 'add'])->name('cart.add');
    Route::delete('/items/{productId}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);
});
