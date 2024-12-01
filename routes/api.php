<?php

use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\Auth\SocialAuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\GameProgressController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PackagePaymentController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\QuestionFeedbackController;
use App\Http\Controllers\Api\Store\StoreOrderController;
use App\Http\Controllers\Api\Store\StoreProductController;
use App\Http\Controllers\Api\Store\StoreCategoryController;
use App\Http\Controllers\Api\SuggestedQuestionController;
use App\Http\Controllers\Api\UserCustomCategoryController;
use App\Http\Controllers\Api\UserCustomQuestionController;
use Illuminate\Support\Facades\Route;

// AUTH ROUTES
Route::group(['prefix' => 'auth'], function () {
    // auth
    Route::post('/login',                       [AuthController::class, 'login']);
    Route::post('/register',                    [AuthController::class, 'register']);
    Route::post('/logout',                      [AuthController::class, 'logout']);
    // phone verification
    Route::post('/verify-phone',                [AuthController::class, 'verifyPhone']);
    Route::post('/resend-otp',                  [AuthController::class, 'resendOtp']);
    // reset password
    Route::post('/forgot-password',             [ForgotPasswordController::class, 'sendOTP']);
    Route::post('/verify-otp',                  [ForgotPasswordController::class, 'verifyOTP']);
    Route::post('/reset-password',              [ForgotPasswordController::class, 'resetPassword']);
    // Social login
    Route::get('/social/{provider}/login',      [SocialAuthController::class, 'redirect']);
    Route::get('/social/{provider}/callback',   [SocialAuthController::class, 'callback']);
    // user data
    Route::get('/user',                         [AccountController::class, 'user']);
    Route::post('/user',                        [AccountController::class, 'updateAccount']);
});


Route::middleware('auth:sanctum')->group(function () {
    // check coupon
    Route::post('/coupons/check',   [CouponController::class, 'check']);
    // games
    Route::get('/games/my-games',   [GameController::class, 'my_games']);
    Route::post('/games/create',    [GameController::class, 'create_game']);
    Route::get('/games/{id}',       [GameController::class, 'get_game']);

    // Game progress
    Route::get('/game-progress/{id}',   [GameProgressController::class, 'show']);
    Route::post('/game-progress',  [GameProgressController::class, 'store']);

    // VAR - question feedback
    Route::post('/question-feedback', [QuestionFeedbackController::class, 'feedback']);
    // Order routes
    Route::post('/orders/create',   [OrderController::class, 'create']);
    Route::get('/orders/callback',  [OrderController::class, 'callback'])->name('orders.tap_callback')->withoutMiddleware('auth:sanctum');
    Route::get('/orders/payment',   [OrderController::class, 'payment_status'])->name('orders.tap_payment_status')->withoutMiddleware('auth:sanctum');
    Route::get('/orders/{id}',      [OrderController::class, 'show']);
    Route::get('/my-orders',        [OrderController::class, 'myOrders']);

    // customized user questions & categories
    Route::apiResource('customized/categories', UserCustomCategoryController::class);
    Route::apiResource('customized/questions', UserCustomQuestionController::class);

    // suggest questions
    Route::apiResource('suggested-questions', SuggestedQuestionController::class);
});

// Store Routes
Route::prefix('store')->group(function () {
    // Store Categories
    Route::get('/categories', [StoreCategoryController::class, 'index']);
    Route::get('/categories/{id}', [StoreCategoryController::class, 'show']);

    // Store Products
    Route::get('/products', [StoreProductController::class, 'index']);
    Route::get('/products/{id}', [StoreProductController::class, 'show']);

    // Tap Payment callback route
    Route::get('/orders/callback', [StoreOrderController::class, 'callback'])->name('store.orders.callback');
    // Store Orders (require authentication)
    Route::get('/my-orders', [StoreOrderController::class, 'myOrders'])->middleware('auth:sanctum');
    Route::post('/orders', [StoreOrderController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/orders/{id}', [StoreOrderController::class, 'show'])->middleware('auth:sanctum');
});


// Site resource APIs
Route::apiResources(
    [
        'packages'          => PackageController::class,
        'categories'        => CategoryController::class,
        'pages'             => PageController::class,
    ],
    ['only' => ['index', 'show']]
);
