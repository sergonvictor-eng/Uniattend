<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendPasswordReset'])->name('password.email');

// Protected Routes
Route::middleware('auth')->group(function () {
    
    // Admin Routes
    Route::middleware('check.role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User Upload
        Route::get('/upload-users', [AdminController::class, 'uploadUsersForm'])->name('upload-users');
        Route::post('/upload-users', [AdminController::class, 'uploadUsers'])->name('upload-users.post');
        
        // Timetable Upload
        Route::get('/upload-timetable', [AdminController::class, 'uploadTimetableForm'])->name('upload-timetable');
        Route::post('/upload-timetable', [AdminController::class, 'uploadTimetable'])->name('upload-timetable.post');
        
        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/generate', [AdminController::class, 'generateReport'])->name('reports.generate');
    });

    // Lecturer Routes
    Route::middleware('check.role:lecturer')->prefix('lecturer')->name('lecturer.')->group(function () {
        Route::get('/dashboard', [LecturerController::class, 'dashboard'])->name('dashboard');
        Route::get('/courses', [LecturerController::class, 'courses'])->name('courses');
        Route::get('/sessions/{session}/qr-code', [LecturerController::class, 'showQRCode'])->name('qr-code');
        Route::get('/sessions/{session}/qr-image', [LecturerController::class, 'getQRCodeImage'])->name('qr-image');
        Route::post('/sessions/start', [LecturerController::class, 'startSession'])
         ->name('lecturer.sessions.start')
         ->middleware(['auth', 'check.role:lecturer']);
         Route::post('/sessions/{session}/end', [LecturerController::class, 'endSession'])
    ->name('lecturer.sessions.end');

        

    });

    // Student Routes
    Route::middleware('check.role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [AttendanceController::class, 'studentDashboard'])->name('dashboard');
        Route::get('/scan', [AttendanceController::class, 'scanPage'])->name('scan');
    });

});