<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

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

// Auth routes
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');
Route::get('/auth/user', [AuthController::class, 'getCurrentUser'])->name('auth.user');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::patch('/cart/{productId}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

// Profile routes (protected with auth middleware)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [ProfileController::class, 'orderDetail'])->name('orders.show');
});

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/shipping-policy', 'shipping-policy')->name('shipping-policy');
Route::view('/returns-exchanges', 'returns-exchanges')->name('returns-exchanges');


