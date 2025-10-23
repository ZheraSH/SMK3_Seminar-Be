<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

// Route::middleware(['auth:sanctum', 'role:school_operator'])->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::apiResource('employees', EmployeeController::class);

    Route::apiResource('majors', MajorController::class)->only(['index', 'show']);
        
// });


