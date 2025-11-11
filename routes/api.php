<?php

use App\Http\Controllers\Api\AttendanceRuleController;
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
use App\Http\Controllers\Api\LessonSchedulesController;
use App\Http\Controllers\Api\RfidController;
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
    Route::prefix('classrooms/{classroom}')->group(function () {
        Route::get('/available-students', [ClassroomController::class,'getAvailableStudents']);
        Route::post('/add-students', [ClassroomController::class, 'addStudents']);
        Route::post('/sync-students', [ClassroomController::class, 'syncStudents']);
        Route::delete('/remove-student/{studentId}', [ClassroomController::class, 'removeStudent']);
    });
    Route::apiResource('classroomStudents', ClassroomStudentsController::class)->only('index'); 
    Route::apiResource('school-years', SchoolYearsController::class)->except(['update']);
    Route::prefix('school-years')->group(function () {
        Route::get('/active', [SchoolYearsController::class, 'active']);
        Route::get('/cron-status', [SchoolYearsController::class, 'cronStatus']);
        Route::patch('/restore/{id}', [SchoolYearsController::class, 'restore']);
    });
    Route::get('/semesters/active', [SemesterController::class, 'active']);
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('lessonHours', LessonHourController::class);
    Route::prefix('lessonHours')->group(function () {
        Route::get('day/{day}', [LessonHourController::class, 'getByDay']);
        Route::get('grouped/days', [LessonHourController::class, 'getAllGroupedByDay']);
    });
    // Route::apiResource('lessonSchedules', LessonSchedulesController::class);
    // Route::prefix('classrooms')->group(function () {
    //     Route::get('/{classroomId}/schedules', [LessonSchedulesController::class, 'getByClassroom']);
    //     Route::get('/{classroomId}/schedules/{day}', [LessonSchedulesController::class, 'getByClassroomAndDay']);
    // });
    // Route::apiResource('attendanceRules', AttendanceRuleController::class)->only(['store', 'show']);
    // Route::prefix('attendanceRules')->group(function () {
    //     Route::get('/day/{day}', [AttendanceRuleController::class, 'getByDay']);
    //     Route::post('/day/{day}', [AttendanceRuleController::class, 'updateByDay']);
    // });
    // Route::apiResource('rfids', RfidController::class);
    // Route::prefix('rfids')->group(function () {
    //     Route::get('/status/used', [RfidController::class, 'used']);
    //     Route::get('/status/not-used', [RfidController::class, 'notUsed']);
    // });
// });
    