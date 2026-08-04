<?php

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Customer\BookingController;
use App\Http\Controllers\Api\V1\Customer\FavoriteController;
use App\Http\Controllers\Api\V1\Customer\GymController;
use App\Http\Controllers\Api\V1\Customer\PaymentController;
use App\Http\Controllers\Api\V1\Customer\ReviewController;
use App\Http\Controllers\Api\V1\Owner\OwnerController;
use App\Http\Controllers\Api\V1\ReferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ---------- Public reference data ----------
    Route::get('/amenities', [ReferenceController::class, 'amenities']);
    Route::get('/cities', [ReferenceController::class, 'cities']);

    // ---------- Auth ----------
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1');
        Route::post('/google', [AuthController::class, 'googleLogin']);
        Route::post('/otp/send', [AuthController::class, 'sendOtp'])->middleware('throttle:5,1');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::get('/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    });

    // ---------- Public gym browsing ----------
    Route::get('/gyms', [GymController::class, 'index']);
    Route::get('/gyms/nearby', [GymController::class, 'nearby']);
    Route::post('/gyms/compare', [GymController::class, 'compare']);
    Route::get('/gyms/{slug}', [GymController::class, 'show']);
    Route::get('/gyms/{gym}/reviews', [ReviewController::class, 'index'])->scopeBindings();

    // ---------- Razorpay webhook (public) ----------
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);

    // ==================================================
    // Authenticated routes
    // ==================================================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // --- Customer ---
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{gym}/toggle', [FavoriteController::class, 'toggle']);

        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings/quote', [BookingController::class, 'quote']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice']);

        Route::post('/payments/verify', [PaymentController::class, 'verify']);

        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::post('/reviews/{review}/like', [ReviewController::class, 'like']);
        Route::post('/reviews/{review}/report', [ReviewController::class, 'report']);

        // --- Gym Owner ---
        Route::middleware('role:gym_owner,admin')->prefix('owner')->group(function () {
            Route::get('/dashboard', [OwnerController::class, 'dashboard']);
            Route::get('/gyms', [OwnerController::class, 'myGyms']);
            Route::post('/gyms', [GymController::class, 'store']);
            Route::get('/bookings', [OwnerController::class, 'bookings']);
            Route::get('/reviews', [OwnerController::class, 'reviews']);
            Route::post('/reviews/{review}/reply', [OwnerController::class, 'replyReview']);
            Route::post('/gyms/{gym}/plans', [OwnerController::class, 'storePlan']);
            Route::patch('/plans/{plan}', [OwnerController::class, 'updatePlan']);
            Route::delete('/plans/{plan}', [OwnerController::class, 'deletePlan']);
        });

        // --- Admin ---
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/users', [AdminController::class, 'users']);
            Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus']);
            Route::get('/gyms/pending', [AdminController::class, 'pendingGyms']);
            Route::post('/gyms/{gym}/approve', [AdminController::class, 'approveGym']);
            Route::post('/gyms/{gym}/reject', [AdminController::class, 'rejectGym']);
            Route::post('/gyms/{gym}/suspend', [AdminController::class, 'suspendGym']);
            Route::get('/analytics/revenue', [AdminController::class, 'revenue']);
        });
    });
});
