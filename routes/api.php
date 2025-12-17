<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('throttle:api.auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/
Route::prefix('email')->middleware('throttle:6,1')->group(function () {
    Route::get('verify/{id}/{hash}', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('verification-notification', [AuthController::class, 'resend'])->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Protected API (Auth + Verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::apiResource('comments', CommentController::class);

    Route::prefix('analytics')->group(function () {
        Route::get('posts', [AnalyticsController::class, 'posts']);
        Route::get('comments', [AnalyticsController::class, 'comments']);
        Route::get('users', [AnalyticsController::class, 'users']);
    });
});

/*
|--------------------------------------------------------------------------
| Meta
|--------------------------------------------------------------------------
*/
Route::prefix('meta')->group(function () {
    Route::get('roles', [MetaController::class, 'roles']);
});
