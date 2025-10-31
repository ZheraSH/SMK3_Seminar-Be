<?php

use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\SchoolYearsController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\ReligionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LevelClassController;
use App\Http\Controllers\Api\ClassroomStudentsController;       
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

// Route::middleware(['auth:sanctum', 'role:school_operator'])->group(function () {
    Route::apiResource('roles', RoleController::class)->only('index');
    Route::apiResource('students', StudentController::class);
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('religions', ReligionController::class)->only(['index', 'show']);
    Route::apiResource('majors', MajorController::class)->only(['index', 'show']);
    Route::apiResource('levelclasses', LevelClassController::class)->only(['index', 'show']);
    Route::apiResource('classrooms', ClassroomController::class);
    //End Point nambah dan hapus siswa ke kelas
    Route::prefix('classrooms/{classroom}')->group(function () {
        Route::get('/available-students', [ClassroomController::class,'getAvailableStudents']);
        Route::post('/add-students', [ClassroomController::class, 'addStudents']);
        Route::post('/sync-students', [ClassroomController::class, 'syncStudents']);
        Route::delete('/remove-student/{studentId}', [ClassroomController::class, 'removeStudent']);
    });
    Route::apiResource('classroomStudents', ClassroomStudentsController::class)->only('index'); 
// });

Route::prefix('school-years')->group(function () {
    Route::get('/', [SchoolYearsController::class, 'index']);
    Route::get('/active', [SchoolYearsController::class, 'active']);
    Route::get('/cron-status', [SchoolYearsController::class, 'cronStatus']);
    Route::post('/', [SchoolYearsController::class, 'store']);
    Route::get('/{id}', [SchoolYearsController::class, 'show']);
    Route::delete('/{id}', [SchoolYearsController::class, 'destroy']);
    Route::patch('/restore/{id}', [SchoolYearsController::class, 'restore']);
});
Route::prefix('semesters')->group(function () {
    Route::get('/', [SemesterController::class, 'index'])->name('semesters.index');
    Route::get('/cron-status', [SemesterController::class, 'cronStatus'])->name('semesters.cronStatus');
    Route::get('/{id}', [SemesterController::class, 'show'])->name('semesters.show');
});
