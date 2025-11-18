<?php

use App\Http\Controllers\Api\StudentLessonScheduleController;
use App\Http\Controllers\Api\AttendanceController;
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
use App\Http\Controllers\Api\RfidTapController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

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
    Route::apiResource('levelClass', LevelClassController::class)->only(['index', 'show']);
    // Classrooms
    Route::apiResource('classrooms', ClassroomController::class);
    Route::prefix('classrooms')->controller(ClassroomController::class)->group(function () {
        Route::get('{classroom}/available-students', 'getAvailableStudents'); // siswa yg bisa ditambahkan
        Route::post('{classroom}/add-students', 'addStudents'); // tambah siswa ke kelas
        Route::post('{classroom}/sync-students', 'syncStudents'); // sinkronisasi siswa
        Route::delete('{classroom}/remove-student/{studentId}', 'removeStudent'); // hapus siswa dari kelas
    });
    // Classroom Students
    Route::apiResource('classroomStudents', ClassroomStudentsController::class)->only('index');
    // School Years
    Route::apiResource('schoolYears', SchoolYearsController::class)->except(['update']);
    Route::patch('school-years/{id}/activate', [SchoolYearsController::class, 'activate']);
    Route::get('school-years/active', [SchoolYearsController::class, 'active'
    ]);
    // Semesters
    Route::prefix('semesters')->controller(SemesterController::class)->group(function () {
        Route::get('active', 'active'); // semester aktif
    });
    // Subjects
    Route::apiResource('subjects', SubjectController::class);
    // Lesson Hours
    Route::apiResource('lessonHours', LessonHourController::class)->except(['update']);
    Route::prefix('lessonHours')->controller(LessonHourController::class)->group(function () {
        Route::get('day/{day}', 'getByDay'); // jam pelajaran berdasarkan hari
        Route::get('grouped/days', 'getAllGroupedByDay'); // semua jam dikelompokkan per hari
    });
    // Lesson Schedules
    Route::apiResource('lessonSchedules', LessonSchedulesController::class);
    Route::prefix('lessonSchedules')->controller(LessonSchedulesController::class)->group(function () {
        Route::get('{classroomId}/schedules', 'getByClassroom'); // jadwal per kelas 
        Route::get('{classroomId}/schedules/{day}', 'getByClassroomAndDay'); // jadwal per kelas dan hari
    });
    // Attendance Rules
    Route::apiResource('attendanceRules', AttendanceRuleController::class)->only(['index','store']);
    Route::prefix('attendanceRules')->controller(AttendanceRuleController::class)->group(function () {
        Route::post('day/{day}', 'updateByDay'); // update aturan absensi per hari
        Route::get('day/{day}', 'getByDay'); // aturan absensi per hari
    });
    // RFID Management
    Route::apiResource('rfids', RfidController::class);
    // RFID Tap Routes
    Route::post('rfidTap', [RfidTapController::class, 'tap']); // absensi tap siswa

// });

// Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    
    // Student Lesson Schedule
    Route::apiResource('students.lesson-schedules', StudentLessonScheduleController::class)->only(['index']);

// });

// Route::middleware(['auth:sanctum', 'role:teacher'])->group(function () {
    
    // Attendance Routes
    Route::apiResource('attendances', AttendanceController::class);
    Route::prefix('attendances')->controller(AttendanceController::class)->group(function () {
        Route::get('classroom/{classroomId}', 'getByClassroom');
        Route::get('student/{studentId}/monthly', 'getStudentMonthly');
        Route::get('student/{studentId}/today', 'getTodayByStudent');
        Route::get('by-date', 'getByDate');
    });

// });

// Route::middleware(['auth:sanctum', 'role:homeroom_teacher'])->group(function () {
    // Tambahkan routes untuk homeroom teacher di sini
// });

// Route::middleware(['auth:sanctum', 'role:counselor'])->group(function () {
    // Tambahkan routes untuk counselor di sini
// });

// Route::middleware(['auth:sanctum', 'role:curriculum_coordinator'])->group(function () {
    // Tambahkan routes untuk curriculum coordinator di sini
// });