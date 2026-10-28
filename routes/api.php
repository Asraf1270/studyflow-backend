<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\TaskController;
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
        // Schedules
        Route::get('/schedules/today', [ScheduleController::class, 'today']);
        Route::apiResource('schedules', ScheduleController::class);
        // Tasks
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
        Route::apiResource('tasks', TaskController::class);
        // Dahsboard
        Route::get('/dashboard', [DashboardController::class, 'show']);
    });
});