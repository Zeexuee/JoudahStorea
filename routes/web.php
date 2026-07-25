<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Models\Category;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

$categorySlugMap = [
    'perfume' => 'j-scent',
    'deodorant' => 'j-skin',
    'bukhur-gaharu' => 'bukhur',
    'linen-spray' => 'linen',
    'kayu-gaharu' => 'j-scent',
];

Route::get('/', function () {
    $categories = \App\Models\Category::with(['products' => function($query) {
        $query->limit(6); // Limit products per category for the homepage
    }])->get()->keyBy('slug');
    
    $videos = \App\Models\HomeVideo::where('is_active', true)
        ->orderBy('sort_order')
        ->get();
        
    $events = \App\Models\Event::where('is_active', true)
        ->orderBy('event_date', 'desc')
        ->take(3)
        ->get();
    
    return view('welcome', [
        'categories' => $categories,
        'videos' => $videos,
        'events' => $events,
    ]);
});

Route::get('/product/{slug}', function ($slug) {
    $product = \App\Models\Product::with([
        'category',
        'reviews' => function ($query) {
            $query->where('is_approved', true)->latest();
        },
    ])->where('slug', $slug)->firstOrFail();

    $verifiedCustomerReviews = \App\Models\Comment::query()
        ->whereHas('order', function ($query) {
            $query->where('status', 'delivered');
        })
        ->whereHas('order.items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })
        ->with('user')
        ->latest()
        ->get()
        ->map(function ($comment) {
            return (object) [
                'name' => $comment->user?->name ?? 'Pelanggan',
                'rating' => $comment->rating,
                'comment' => $comment->content,
                'created_at' => $comment->created_at,
                'is_approved' => true,
                'is_verified_purchase' => true,
            ];
        });

    $product->setRelation(
        'reviews',
        $product->reviews->concat($verifiedCustomerReviews)
    );

    $relatedProducts = \App\Models\Product::where('category_id', $product->category_id)
        ->where('id', '!=', $product->id)
        ->inRandomOrder()
        ->take(4)
        ->get();

    return view('product.detail', ['product' => $product, 'relatedProducts' => $relatedProducts]);
})->name('product.detail');

Route::get('/category/{slug}', function (string $slug) use ($categorySlugMap) {
    $canonicalSlug = $categorySlugMap[$slug] ?? $slug;
    $category = Category::where('slug', $canonicalSlug)->with('products')->first();

    if ($canonicalSlug === 'oud') {
        if (!$category || $category->products->isEmpty()) {
            $products = \App\Models\Product::where('name', 'LIKE', '%Oud%')
                ->orWhere('category_id', $category?->id)
                ->get();
            
            if ($products->isEmpty()) {
                $allProducts = \App\Models\Product::take(4)->get();
                $products = $allProducts;
            }

            if ($category) {
                $category->setRelation('products', $products);
            } else {
                $dummyCategory = new Category([
                    'name' => 'Royal Oud Collection',
                    'slug' => 'oud',
                    'description' => 'Kemewahan tiada tara dari kayu Oud pilihan terbaik. Koleksi mahakarya beraroma kayu gaharu yang murni, hangat, dan sangat berkelas.',
                    'hero_image' => 'images/catalog/oud_hero.png',
                ]);
                $dummyCategory->id = 999;
                $dummyCategory->setRelation('products', $products);
                $category = $dummyCategory;
            }
        }

        return view('category.oud', ['category' => $category]);
    }

    if (!$category) {
        abort(404);
    }

    if ($canonicalSlug !== $slug) {
        return redirect()->route('category.show', $canonicalSlug, 301);
    }

    return view('category.show', ['category' => $category]);
})->name('category.show');

Route::view('/distributors', 'distributors')->name('distributors.index');

Route::get('/event/{slug}', [EventController::class, 'show'])->name('event.detail');

// Auth routes
Route::get('/login', function () {
    return redirect('/');
})->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::match(['get', 'post'], '/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth');
Route::get('/auth/user', [AuthController::class, 'getCurrentUser'])->name('auth.user');

// Email Verification routes
Route::get('/email/verify', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return redirect('/profile');
    }
    
    // Automatically trigger OTP sending (handled with cooldown inside service)
    $otpService = app(\App\Services\OtpVerificationService::class);
    $otpService->sendOtp($request->user(), 'email');

    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::post('/email/verify', function (Request $request) {
    $request->validate([
        'code' => 'required|string|size:6',
    ]);

    $otpService = app(\App\Services\OtpVerificationService::class);
    $result = $otpService->verifyOtp($request->user(), 'email', $request->code);

    if ($result['success']) {
        return redirect('/profile')->with('success', 'Email Anda berhasil diverifikasi!');
    }

    return back()->with('error', $result['message']);
})->middleware('auth')->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::delete('/cart/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::patch('/cart/{productId}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

// Mock payment checkout (for testing - public route) - MUST BE BEFORE auth group
Route::get('/payment/mock-checkout/{external_id}', function ($external_id) {
    return view('payment.mock-checkout', ['external_id' => $external_id]);
})->name('payment.mock-checkout');

// Mock payment verification (public route, but controller checks auth) - MUST BE BEFORE auth group
Route::get('/payment/verify-mock', [PaymentController::class, 'verifyMock'])->name('payment.verify-mock');

// Payment callbacks (no auth required) - MUST BE BEFORE auth group
Route::post('/payment/callback/mindtrans', [PaymentController::class, 'callback'])->name('payment.callback');

// Public signed delivery confirmation link from WA/email reminders
Route::get('/delivery/confirm/{order}', [ProfileController::class, 'confirmDeliveredFromLink'])
    ->middleware('signed')
    ->name('delivery.confirm');

// Profile routes (protected with auth middleware)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('orders.index');
    Route::get('/orders/{order}', [ProfileController::class, 'orderDetail'])->name('orders.show');
    Route::post('/orders/{order}/confirm-delivered', [ProfileController::class, 'confirmDelivered'])->name('orders.confirmDelivered');
    
    // Comment routes
    Route::post('/orders/{order}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/orders/{order}/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/orders/{order}/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    Route::post('/verification/otp/send', [VerificationController::class, 'sendOtp'])->name('verification.otp.send');
    Route::post('/verification/otp/verify', [VerificationController::class, 'verifyOtp'])->name('verification.otp.verify');

    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/cities', [CheckoutController::class, 'getCities'])->name('checkout.getCities');
    Route::get('/checkout/shipping-costs', [CheckoutController::class, 'getShippingCosts'])->name('checkout.getShippingCosts');

    // Payment routes (with numeric constraint to avoid catching verify-mock)
    Route::get('/payment/custom/{payment}', [PaymentController::class, 'showCustom'])->name('payment.custom');
    Route::post('/payment/custom/{payment}/process', [PaymentController::class, 'processCustom'])->name('payment.custom.process');
    Route::get('/payment/check-status/{payment}', [PaymentController::class, 'checkPaymentStatus'])->name('payment.checkPaymentStatus');
    Route::get('/payment/{order}', [PaymentController::class, 'show'])->where('order', '[0-9]+')->name('payment.show');
    Route::post('/payment/{order}/process', [PaymentController::class, 'process'])->where('order', '[0-9]+')->name('payment.process');
    Route::get('/payment/{order}/verify', [PaymentController::class, 'verify'])->where('order', '[0-9]+')->name('payment.verify');
    Route::get('/payment/{order}/status', [PaymentController::class, 'checkStatus'])->where('order', '[0-9]+')->name('payment.checkStatus');
    Route::post('/payment/{order}/cancel', [PaymentController::class, 'cancel'])->where('order', '[0-9]+')->name('payment.cancel');
});

// Admin routes (protected with auth and admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminOrderController::class, 'dashboard'])->name('dashboard');
    
    // Order management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');
    Route::post('/orders/{order}/shipping-status', [AdminOrderController::class, 'updateShippingStatus'])->name('orders.updateShippingStatus');
    Route::post('/orders/{order}/confirm-delivered', [AdminOrderController::class, 'confirmDelivered'])->name('orders.confirmDelivered');
    Route::post('/orders/{order}/send-delivery-reminder', [AdminOrderController::class, 'sendDeliveryReminder'])->name('orders.sendDeliveryReminder');
    Route::post('/orders/{order}/cancel', [AdminOrderController::class, 'cancelOrder'])->name('orders.cancel');

});
// Test routes (remove in production)
Route::middleware(['auth', 'admin'])->prefix('test')->group(function () {
    Route::get('/auto-update/{orderId}', [\App\Http\Controllers\TestAutoUpdateController::class, 'testPaymentAutoUpdate'])->name('test.auto-update');
    Route::get('/check-status/{orderId}', [\App\Http\Controllers\TestAutoUpdateController::class, 'checkStatus'])->name('test.check-status');
    Route::get('/logs', [\App\Http\Controllers\TestAutoUpdateController::class, 'testLogs'])->name('test.logs');
});

Route::get('/test/rajaongkir', function () {
    $service = new \App\Services\RajaongkirService();
    
    try {
        $provinces = $service->getProvinces();
        return response()->json([
            'status' => 'success',
            'message' => 'Rajaongkir API is working!',
            'provinces_count' => count($provinces),
            'sample_provinces' => array_slice($provinces, 0, 5, true),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error' => class_basename($e),
        ], 500);
    }
});

Route::get('/test/cities/{provinceId}', function ($provinceId) {
    $service = new \App\Services\RajaongkirService();
    
    try {
        $cities = $service->getCitiesByProvince($provinceId);
        dd([
            'province_id' => $provinceId,
            'success' => true,
            'cities_count' => count($cities),
            'sample_cities' => array_slice($cities, 0, 5, true),
            'raw_response' => http_build_query(['province_id' => $provinceId]),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error' => class_basename($e),
        ], 500);
    }
});

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/shipping-policy', 'shipping-policy')->name('shipping-policy');
Route::view('/returns-exchanges', 'returns-exchanges')->name('returns-exchanges');
Route::view('/faq', 'faq')->name('faq');


