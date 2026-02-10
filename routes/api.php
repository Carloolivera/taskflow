<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Tasks (nested under projects)
    Route::apiResource('projects.tasks', TaskController::class);

    // Tags - read for all, write for admin
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{tag}', [TagController::class, 'show']);

    // Admin only
    Route::middleware('admin')->group(function () {
        Route::get('/test-admin', fn () => response()->json(['message' => 'OK']));
        Route::post('/tags', [TagController::class, 'store']);
        Route::put('/tags/{tag}', [TagController::class, 'update']);
        Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
    });
});
