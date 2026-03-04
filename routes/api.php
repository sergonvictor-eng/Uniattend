<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::post('/login', [AuthController::class, 'apiLogin']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    
    // Admin APIs
    Route::middleware('check.role:admin')->prefix('admin')->group(function () {
        Route::post('/upload-users', [AdminController::class, 'uploadUsers']);
        Route::post('/upload-timetable', [AdminController::class, 'uploadTimetable']);
        Route::get('/reports', [AdminController::class, 'generateReport']);
    });

    // Lecturer APIs
    Route::middleware('check.role:lecturer')->prefix('sessions')->group(function () {
        Route::post('/start', [LecturerController::class, 'startSession']);
        Route::post('/{session}/end', [LecturerController::class, 'endSession']);
        Route::get('/active', [LecturerController::class, 'activeSessions']);
        Route::get('/{session}', [LecturerController::class, 'sessionDetails']);
    });

    // Attendance APIs
    Route::prefix('attendance')->group(function () {
        Route::post('/scan', [AttendanceController::class, 'scanQRCode']);
        Route::get('/student', [AttendanceController::class, 'studentAttendance']);
        Route::get('/session/{session}', [AttendanceController::class, 'sessionAttendance']);
    });
});
