<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\TermsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Image Routes
|--------------------------------------------------------------------------
*/
Route::get('/images/{path}', function ($path) {
    $path = storage_path('app/public/' . $path);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->where('path', '.*');

/*
|--------------------------------------------------------------------------
| FCM Token Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('fcm')->group(function () {
    Route::post('/token', [FcmTokenController::class, 'saveToken']);
    Route::delete('/token', [FcmTokenController::class, 'removeToken']);
});

/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/firebase', [AuthController::class, 'firebaseLogin']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/send-verification-email', [AuthController::class, 'sendVerificationEmail']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/email', [AccountController::class, 'updateEmail']);
        Route::delete('/account', [AccountController::class, 'deleteAccount']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/firebase/set-password', [AuthController::class, 'setFirebasePassword']);
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
| Regions Routes (Authenticated: Create Neighborhood)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('regions')->group(function () {
    Route::post('/', [RegionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Terms Routes (Public: View, Admin: Update)
|--------------------------------------------------------------------------
*/
Route::get('/terms', [TermsController::class, 'show']);
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::put('/terms', [TermsController::class, 'update']);
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
    Route::get('/types', [PropertyController::class, 'types']);
    Route::get('/{id}', [PropertyController::class, 'show'])->where('id', '[0-9]+');
    Route::get('/my-properties', [PropertyController::class, 'myProperties'])->middleware('auth:sanctum');
    Route::post('/', [PropertyController::class, 'store'])->middleware('auth:sanctum');
    Route::put('/{id}', [PropertyController::class, 'update'])->where('id', '[0-9]+')->middleware('auth:sanctum');
    Route::delete('/{id}', [PropertyController::class, 'destroy'])->where('id', '[0-9]+')->middleware('auth:sanctum');
    Route::delete('/{id}/images/{imageId}', [PropertyController::class, 'deleteImage'])->where('id', '[0-9]+')->middleware('auth:sanctum');
    Route::post('/{id}/rented', [PropertyController::class, 'markAsRented'])->where('id', '[0-9]+')->middleware('auth:sanctum');
    Route::post('/{id}/sold', [PropertyController::class, 'markAsSold'])->where('id', '[0-9]+')->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| Favorites Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/', [FavoriteController::class, 'index']);
    Route::post('/{propertyId}', [FavoriteController::class, 'store']);
    Route::delete('/{propertyId}', [FavoriteController::class, 'destroy']);
    Route::get('/{propertyId}/check', [FavoriteController::class, 'check']);
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
        // FCM
        Route::post('/send-notification', [FcmTokenController::class, 'sendNotification']);
        
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
        Route::put('/regions/{id}', [RegionController::class, 'update']);
        Route::delete('/regions/{id}', [RegionController::class, 'destroy']);
        
        // Statistics
        Route::get('/statistics', [AdminController::class, 'statistics']);
        
        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/recent-activities', [AdminController::class, 'recentActivities']);
            Route::get('/chart-data', [AdminController::class, 'chartData']);
            Route::get('/properties-by-region', [AdminController::class, 'propertiesByRegion']);
            Route::get('/properties-by-type', [AdminController::class, 'propertiesByType']);
            Route::get('/properties-summary', [AdminController::class, 'propertiesSummary']);
            Route::get('/users-registration', [AdminController::class, 'usersRegistration']);
            Route::get('/top-active-regions', [AdminController::class, 'topActiveRegions']);
        });
    });
});

