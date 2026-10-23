<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 1. Health & Anti-CSRF
Route::get('/', [HealthController::class, 'health']);
Route::get('/health', [HealthController::class, 'health']);
Route::get('/csrf-token', [HealthController::class, 'csrfToken']);

// 2. Categories
Route::get('/categories', [CategoryController::class, 'index']);

// 3. Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// 4. Coupons & Promo Validation
Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
Route::post('/promo/validate', [CouponController::class, 'validateCoupon']);

// 5. Customer Reviews
Route::get('/reviews', [ReviewController::class, 'index']);
Route::post('/reviews', [ReviewController::class, 'store']);

// 6. User Addresses
Route::get('/addresses', [AddressController::class, 'index']);
Route::post('/addresses', [AddressController::class, 'store']);
Route::delete('/addresses', [AddressController::class, 'destroy']);
Route::match(['post', 'put'], '/addresses/default', [AddressController::class, 'setDefault']);

// 7. Wishlists & Favorites
Route::get('/wishlists', [WishlistController::class, 'index']);
Route::post('/wishlists', [WishlistController::class, 'store']);
Route::delete('/wishlists', [WishlistController::class, 'destroy']);
Route::post('/wishlists/items', [WishlistController::class, 'addItem']);
Route::delete('/wishlists/items', [WishlistController::class, 'removeItem']);
Route::get('/favorites', [WishlistController::class, 'favorites']);
Route::post('/favorites/toggle', [WishlistController::class, 'toggleFavorite']);

// 8. Authentication & Account
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::match(['post', 'put'], '/auth/profile', [AuthController::class, 'profile']);

// Authenticated user check via Sanctum
Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'status' => 200,
        'data' => $request->user(),
    ]);
})->middleware('auth:sanctum');

// 9. Orders & Live Tracking
Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/track', [OrderController::class, 'track']);

// 10. Fallback for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'status' => 404,
        'error' => [
            'message' => 'Endpoint not found',
        ],
    ], 404, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
});
