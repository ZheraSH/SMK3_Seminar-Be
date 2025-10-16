<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\StudentApiController;

Route::post('login', [LoginApiController::class, 'login']);

Route::prefix('student')->group(function () {
    // CRUD
    Route::post('students', [StudentApiController::class, 'handle']);

    // Custom endpoint
    Route::get('dashboard/{user}', [StudentApiController::class, 'dashboard']);
    Route::get('history-attendance/{user}', [StudentApiController::class, 'historyAttendance']);
    Route::get('lesson-schedule/{user}', [StudentApiController::class, 'lessonSchedule']);
    Route::get('class-student/{user}', [StudentApiController::class, 'classStudent']);
    Route::get('detail-profile/{user}', [StudentApiController::class, 'detailProfile']);
    Route::get('top-points', [StudentApiController::class, 'topPoints']);
});
