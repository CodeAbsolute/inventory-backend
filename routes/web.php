<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Verify Email API route
Route::get('/api/v1/verifyEmail/{token}', [AuthController::class, 'verifyMail']);