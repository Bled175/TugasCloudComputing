<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\QrScanController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Attendance Routes
    Route::prefix('attendance')->group(function () {
        Route::get('/daily-recap', [AttendanceController::class, 'dailyRecap'])->name('attendance.daily-recap');
        Route::get('/student/{studentId}', [AttendanceController::class, 'studentHistory'])->name('attendance.student-history');
        Route::get('/report/date-range', [AttendanceController::class, 'dateRangeReport'])->name('attendance.date-range-report');
        Route::get('/report/student/{studentId}', [AttendanceController::class, 'studentDateRangeReport'])->name('attendance.student-date-range-report');

        // API Endpoints
        Route::get('/api/student/{studentId}/stats', [AttendanceController::class, 'studentStats'])->name('api.attendance.student-stats');
        Route::get('/api/range-stats', [AttendanceController::class, 'rangeStats'])->name('api.attendance.range-stats');
    });

    // QR Scan endpoint for sekretaris
    Route::post('/scan-qr', [QrScanController::class, 'scan']);
});

require __DIR__.'/auth.php';
