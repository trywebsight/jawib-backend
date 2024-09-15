<?php

use App\Http\Controllers\Api\Auth\AccountController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PackagePaymentController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\QuestionFeedbackController;
use Illuminate\Support\Facades\Route;

// auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
// get user data route
Route::get('/auth/user', [AccountController::class, 'user'])->middleware('auth:sanctum');
// forgot password
Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'sendOTP']);
Route::post('/auth/verify-otp', [ForgotPasswordController::class, 'verifyOTP']);
Route::post('/auth/reset-password', [ForgotPasswordController::class, 'resetPassword']);

// Site resource APIs
Route::apiResources(
    [
        'packages' => PackageController::class,
        'categories' => CategoryController::class,
        'pages' => PageController::class,
    ],
    ['only' => ['index', 'show']]
);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AccountController::class, 'user']);
    Route::put('/user/update-info', [AccountController::class, 'update_info']);

    // buy package
    Route::get('/packages/{id}/buy', [PackagePaymentController::class, 'buy']);
    // games
    Route::get('/games/my-games', [GameController::class, 'my_games']);
    Route::post('/games/create', [GameController::class, 'create_game']);
    Route::get('/games/{id}', [GameController::class, 'get_game']);

    // VAR - question feedback
    Route::post('/question-feedback', [QuestionFeedbackController::class, 'feedback']);
});
Route::get('/tap/callback', [PackagePaymentController::class, 'callback'])->name('tap_callback');
