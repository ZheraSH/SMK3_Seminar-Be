<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\StudentController;

Route::post('login', [LoginController::class, 'login']);

// Route::middleware(['auth:sanctum', 'role:school_operator'])->group(function () {
    Route::apiResource('students', StudentController::class);
// });


