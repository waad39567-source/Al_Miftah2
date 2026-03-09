<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RegionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| Public Regions Routes (View & Search Only)
|--------------------------------------------------------------------------
*/
Route::prefix('regions')->group(function () {
    Route::get('/', [RegionController::class, 'index']);
    Route::get('/{id}', [RegionController::class, 'show']);
    Route::get('/types/list', [RegionController::class, 'types']);
    Route::get('/root/list', [RegionController::class, 'rootRegions']);
    Route::get('/{id}/children', [RegionController::class, 'children']);
});

/*
|--------------------------------------------------------------------------
| Properties Routes (Public: View Only)
|--------------------------------------------------------------------------
*/
Route::prefix('properties')->group(function () {
    Route::get('/', [PropertyController::class, 'index']);
    Route::get('/{id}', [PropertyController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Contact Routes (Public: Create Only)
|--------------------------------------------------------------------------
*/
Route::post('/contact', [ContactController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Admin routes
    Route::get('/users', [AdminController::class, 'getUsers']);
    
    Route::prefix('admin')->group(function () {
        // Properties Management
        Route::get('/properties', [AdminController::class, 'getProperties']);
        Route::post('/properties/{id}/approve', [AdminController::class, 'approveProperty']);
        Route::post('/properties/{id}/reject', [AdminController::class, 'rejectProperty']);
        
        // Contact Requests
        Route::get('/contact-requests', [AdminController::class, 'getContactRequests']);
        
        // Regions CRUD (Admin Only)
        Route::post('/regions', [RegionController::class, 'store']);
        Route::put('/regions/{id}', [RegionController::class, 'update']);
        Route::delete('/regions/{id}', [RegionController::class, 'destroy']);
        
        // Statistics
        Route::get('/statistics', [AdminController::class, 'statistics']);
    });
});
