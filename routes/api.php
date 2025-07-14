<?php

use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::apiResource('users', UserController::class);
    
    // Task routes
    Route::get('/tasks/my', [TaskController::class, 'myTasks']);
    Route::apiResource('tasks', TaskController::class);
    
    // Email status route (admin only)
    Route::get('/email-status', [TaskController::class, 'emailStatus']);
    
    // Queue monitoring routes (admin only)
    Route::prefix('queue')->group(function () {
        Route::get('/stats', [QueueController::class, 'stats']);
        Route::get('/pending-emails', [QueueController::class, 'pendingEmails']);
        Route::get('/failed-emails', [QueueController::class, 'failedEmails']);
        Route::post('/retry/{jobId}', [QueueController::class, 'retryJob']);
        Route::delete('/failed-jobs', [QueueController::class, 'clearFailedJobs']);
    });
});
