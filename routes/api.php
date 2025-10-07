<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ReligionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\RfidAttendanceController;
use App\Http\Controllers\Api\LessonAttendanceController;
use App\Http\Controllers\SchoolYearController;


Route::post('/login', [AuthController::class, 'login']);

// Public routes

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // User management (only admin can access)
    Route::middleware(['role:operator sekolah'])->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('/users/{id}/assign-role', [UserController::class, 'assignRole']);
        Route::post('/users/{id}/remove-role', [UserController::class, 'removeRole']);
    });
    
    // Employee management (admin and staff TU can access)
    Route::middleware(['role:operator sekolah,staff TU'])->group(function () {
        Route::apiResource('employees', EmployeeController::class);
    });
    
    // Student management (admin, staff TU, and teachers can access)
    Route::middleware(['role:operator sekolah|staff TU|guru'])->group(function () {
        Route::apiResource('students', StudentController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendance/rfid-tap', [RfidAttendanceController::class, 'tap']);
        Route::get('attendance/lesson', [LessonAttendanceController::class, 'index']);
        Route::post('attendance/lesson/mark', [LessonAttendanceController::class, 'mark']);
    });
});


//Test fetching data
Route::get ('/nando',action: function():JsonResponse{
    return response()->json(data: ['message' => 'nando anjay']);
});
//Test fetching data



//tahun ajaran
Route::apiResource('school-years', SchoolYearController::class);