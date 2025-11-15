<?php

use App\Http\Controllers\Api\StudentLessonScheduleController;
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
// Roles
    Route::apiResource('roles', RoleController::class)->only('index');

    // Students
    Route::apiResource('students', StudentController::class);

    // Employees
    Route::apiResource('employees', EmployeeController::class);

    // Religions        
    Route::apiResource('religions', ReligionController::class)->only(['index', 'show']);

    // Majors
    Route::apiResource('majors', MajorController::class)->only(['index', 'show']);

    // Level Classes
    Route::apiResource('levelclasses', LevelClassController::class)->only(['index', 'show']);

    // Classrooms
    Route::apiResource('classrooms', ClassroomController::class);

    // Classroom - Custom Routes
    Route::prefix('classrooms/{classroom}')->group(function () {
        Route::get('/available-students', [ClassroomController::class, 'getAvailableStudents']); // siswa yg bisa ditambahkan
        Route::post('/add-students', [ClassroomController::class, 'addStudents']); // tambah siswa ke kelas
        Route::post('/sync-students', [ClassroomController::class, 'syncStudents']); // sinkronisasi siswa
        Route::delete('/remove-student/{studentId}', [ClassroomController::class, 'removeStudent']); // hapus siswa dari kelas
    });

    // Classroom Students
    Route::apiResource('classroomStudents', ClassroomStudentsController::class)->only('index');

 Route::prefix('school-years')->controller(SchoolYearsController::class)->group(function () {
    Route::get('/active', 'active');
    Route::get('/cron-status', 'cronStatus');
});

Route::apiResource('school-years', SchoolYearsController::class)->except(['update']);

    

    // Semesters
    Route::get('semesters/active', [SemesterController::class, 'active']); // semester aktif

    // Subjects
    Route::apiResource('subjects', SubjectController::class);


    // Lesson Hours
    Route::apiResource('lessonHours', LessonHourController::class)->except(['update']);
    Route::prefix('lessonHours')->group(function () {
        Route::get('day/{day}', [LessonHourController::class, 'getByDay']); // jam pelajaran berdasarkan hari
        Route::get('grouped/days', [LessonHourController::class, 'getAllGroupedByDay']); // semua jam dikelompokkan per hari
    });

    // Lesson Schedules
    Route::apiResource('lessonSchedules', LessonSchedulesController::class);
    Route::prefix('lessonSchedules')->group(function () {
        Route::get('/{classroomId}/schedules', [LessonSchedulesController::class, 'getByClassroom']); // jadwal per kelas
        Route::get('/{classroomId}/schedules/{day}', [LessonSchedulesController::class, 'getByClassroomAndDay']); // jadwal per kelas dan hari
    });

    // Attendance Rules
    Route::apiResource('attendanceRules', AttendanceRuleController::class)->only(['index','store']);
    Route::prefix('attendanceRules')->group(function () {
        Route::post('/day/{day}', [AttendanceRuleController::class, 'updateByDay']); // update aturan absensi per hari
        Route::get('/day/{day}', [AttendanceRuleController::class, 'getByDay']); // aturan absensi per hari
    });

    //Student Lesson Schedule
Route::apiResource('students.lesson-schedules', StudentLessonScheduleController::class)
    ->only(['index']);

    // Rfid
    // Route::apiResource('rfids', RfidController::class);
    // Route::prefix('rfids')->group(function () {
    //     Route::get('/status/used', [RfidController::class, 'used']);
    //     Route::get('/status/not-used', [RfidController::class, 'notUsed']);
    // });
// });
    