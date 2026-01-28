<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (tidak perlu auth)
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (perlu token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth endpoints
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);

    // Student endpoints
    Route::get('/dashboard', [StudentController::class, 'dashboard']);
    Route::get('/attendances', [StudentController::class, 'getAttendances']);
    Route::get('/qr-code', [StudentController::class, 'getQrCode']);
    Route::get('/profile', [StudentController::class, 'getProfile']);
    Route::put('/profile', [StudentController::class, 'updateProfile']);
});
