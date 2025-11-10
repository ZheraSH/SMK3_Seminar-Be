<?php

use App\Http\Controllers\Api\LessonHourController;
use App\Http\Controllers\Api\SubjectController;
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

Route::apiResource('school-years', SchoolYearsController::class)->except(['update']);
Route::prefix('school-years')->group(function () {
    Route::get('/active', [SchoolYearsController::class, 'active'])->name('school-years.active');
    Route::get('/cron-status', [SchoolYearsController::class, 'cronStatus'])->name('school-years.cron-status');
    Route::patch('/restore/{id}', [SchoolYearsController::class, 'restore'])->name('school-years.restore');
});

Route::apiResource('semesters', SemesterController::class)->only(['index', 'show']);
Route::prefix('semesters')->group(function () {
    Route::get('/active', [SemesterController::class, 'active'])->name('semesters.active');
    Route::get('/cron-status', [SemesterController::class, 'cron-status'])->name('semesters.cron-status');
});


//Route::prefix('subjects')->group(function () {
    //Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
    //Route::get('/{id}', [SubjectController::class, 'show'])->name('subjects.show');
    //Route::post('/', [SubjectController::class, 'store'])->name('subjects.store');
    //Route::put('/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    //Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');



Route::apiResource('lesson-hours', LessonHourController::class);
