<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login',    [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me',           [ProfileController::class, 'show']);
        Route::patch('/me',         [ProfileController::class, 'update']);
        Route::post('/me/password', [ProfileController::class, 'changePassword']);
        Route::apiResource('courses', CourseController::class);
    });
});