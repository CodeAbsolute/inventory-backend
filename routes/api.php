<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryResourceController;
use App\Http\Controllers\ProductResourceController;
use App\Http\Controllers\UserResourceController;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\CategoryController;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\ProductController;

// verified Users API route
// Route::middleware('verified')->group(function () {
// Auth API route
Route::post('/v1/login', [AuthController::class, 'login']);

// Forgot Password API route
Route::post('/v1/forgotPassword', [AuthController::class, 'forgotPassword']);

// Reset Password API route
Route::put('/v1/resetPassword/{token}', [AuthController::class, 'resetPassword']);
// });

Route::middleware(['auth:api'])->prefix('v1')->group(function () {

    Route::resource('users', UserResourceController::class);

    Route::resource('products', ProductResourceController::class);

    Route::resource('category', CategoryResourceController::class);

    Route::get('user', [AuthController::class, 'getLoggedInUserData']);

    Route::get('logout', [AuthController::class, 'logout']);

});

/*
// User API routes
Route::middleware(['auth:api'])->group(function () {
    // Logout API route
    Route::get('/v1/logout', [AuthController::class, 'logout']);

    // Add User API route
    Route::post('/v1/addUser', [UserController::class, 'addUser']);

    // Logged In User data API route
    Route::get('/v1/user', [UserController::class, 'user']);

    // Single User data API route
    Route::get('/v1/user/{id}', [UserController::class, 'singleUser']);

    // All Users data API route
    Route::get('/v1/users', [UserController::class, 'allUsers']);

    // Delete User API route
    Route::delete('/v1/user/{id}', [UserController::class, 'deleteUser']);

});


// Product API routes
Route::middleware(['auth:api'])->group(function () {

    // Add Product API route
    Route::post('/v1/addProduct', [ProductController::class, 'addProduct']);

    // Get All Products data API route
    Route::get('/v1/products', [ProductController::class, 'allProducts']);

    // Get Specific Product data API route
    Route::get('/v1/product/{id}', [ProductController::class, 'singleProduct']);

    // Update Product API route
    Route::put('/v1/product/{id}', [ProductController::class, 'updateProduct']);

    // Delete Product API route
    Route::delete('/v1/product/{id}', [ProductController::class, 'deleteProduct']);
});

// Category API routes
Route::middleware('auth:api')->group(function () {

    // Add Category API route
    Route::post('/v1/addCategory', [CategoryController::class, 'addCategory']);

    // Get All Categories data API route
    Route::get('/v1/categories', [CategoryController::class, 'allCategories']);

    // Delete Category API route
    Route::delete('/v1/category/{id}', [CategoryController::class, 'deleteCategory']);
});
*/