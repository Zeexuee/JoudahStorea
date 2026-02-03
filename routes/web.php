<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::get('/', function () {
    $categories = \App\Models\Category::with(['products' => function($query) {
        $query->limit(6); // Limit products per category for the homepage
    }])->get()->keyBy('slug');
    
    return view('welcome', ['categories' => $categories]);
});

Route::get('/product/{slug}', function ($slug) {
    $product = \App\Models\Product::where('slug', $slug)->firstOrFail();
    $relatedProducts = \App\Models\Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->inRandomOrder()
        ->take(4)
        ->get();

    return view('product.detail', ['product' => $product, 'relatedProducts' => $relatedProducts]);
})->name('product.detail');

Route::get('/category/{slug}', function ($slug) {
    $category = \App\Models\Category::where('slug', $slug)->with('products')->firstOrFail();
    return view('category.show', ['category' => $category]);
})->name('category.show');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::patch('/cart/{productId}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/shipping-policy', 'shipping-policy')->name('shipping-policy');
Route::view('/returns-exchanges', 'returns-exchanges')->name('returns-exchanges');


