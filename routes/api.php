<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RegionController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/promote-to-admin', [AuthController::class, 'promoteToAdmin']);
        Route::post('/users', [AuthController::class, 'createUser']);
    });
});

Route::get('/regions', [RegionController::class, 'index']);
Route::get('/regions/{id}', [RegionController::class, 'show']);

Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);

Route::post('/contact', [ContactController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [AdminController::class, 'getUsers']);
    
    Route::prefix('admin')->group(function () {
        Route::get('/properties', [AdminController::class, 'getProperties']);
        Route::post('/properties/{id}/approve', [AdminController::class, 'approveProperty']);
        Route::post('/properties/{id}/reject', [AdminController::class, 'rejectProperty']);
        Route::get('/contact-requests', [AdminController::class, 'getContactRequests']);
        Route::post('/regions', [AdminController::class, 'createRegion']);
        Route::put('/regions/{id}', [AdminController::class, 'updateRegion']);
        Route::delete('/regions/{id}', [AdminController::class, 'deleteRegion']);
        Route::get('/statistics', [AdminController::class, 'statistics']);
    });
});
