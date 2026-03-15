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
    });
});

/*
|--------------------------------------------------------------------------
| Public Regions Routes (View & Search Only)
|--------------------------------------------------------------------------
*/
Route::prefix('regions')->group(function () {
    Route::get('/', [RegionController::class, 'index']);
    Route::get('/tree', [RegionController::class, 'tree']);
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
    Route::get('/search', [PropertyController::class, 'search']);
    Route::get('/advanced-search', [PropertyController::class, 'advancedSearch']);
    Route::get('/{id}', [PropertyController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Protected Property Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('properties')->group(function () {
    Route::post('/', [PropertyController::class, 'store']);
    Route::put('/{id}', [PropertyController::class, 'update']);
    Route::delete('/{id}', [PropertyController::class, 'destroy']);
    Route::get('/my-properties', [PropertyController::class, 'myProperties']);
    Route::delete('/{id}/images/{imageId}', [PropertyController::class, 'deleteImage']);
    Route::post('/{id}/rented', [PropertyController::class, 'markAsRented']);
    Route::post('/{id}/sold', [PropertyController::class, 'markAsSold']);
});

/*
|--------------------------------------------------------------------------
| Contact Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('contact')->group(function () {
    Route::post('/', [ContactController::class, 'store']);
    Route::get('/my-requests', [ContactController::class, 'myRequests']);
    Route::get('/my-received', [ContactController::class, 'myReceivedRequests']);
    Route::get('/status/{propertyId}', [ContactController::class, 'checkStatus']);
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    Route::prefix('admin')->group(function () {
        // Users Management
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users/create', [AuthController::class, 'createUser']);
        Route::get('/users/unverified', [AdminController::class, 'getUnverifiedUsers']);
        Route::post('/users/{id}/verify', [AdminController::class, 'verifyUser']);
        Route::post('/users/{id}/ban', [AdminController::class, 'banUser']);
        Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser']);
        Route::post('/users/{id}/toggle-active', [AdminController::class, 'toggleUserActive']);
        
        // Properties Management
        Route::get('/properties', [AdminController::class, 'getProperties']);
        Route::post('/properties/{id}/approve', [AdminController::class, 'approveProperty']);
        Route::post('/properties/{id}/reject', [AdminController::class, 'rejectProperty']);
        
        // Contact Requests
        Route::get('/contact-requests', [AdminController::class, 'getContactRequests']);
        Route::post('/contact-requests/{id}/approve', [AdminController::class, 'approveContactRequest']);
        Route::post('/contact-requests/{id}/reject', [AdminController::class, 'rejectContactRequest']);
        
        // Regions CRUD (Admin Only)
        Route::post('/regions', [RegionController::class, 'store']);
        Route::put('/regions/{id}', [RegionController::class, 'update']);
        Route::delete('/regions/{id}', [RegionController::class, 'destroy']);
        
        // Statistics
        Route::get('/statistics', [AdminController::class, 'statistics']);
    });
});
