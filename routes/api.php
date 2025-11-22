<?php

use App\Http\Controllers\Api\Teacher\AttendanceController;
use App\Http\Controllers\Api\AttendanceRuleController;
use App\Http\Controllers\Api\ClassroomController;
use App\Http\Controllers\Api\ClassroomStudentsController;
use App\Http\Controllers\Api\Counselor\CounselorAttendancePermissionController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LessonHourController;
use App\Http\Controllers\Api\LessonSchedulesController;
use App\Http\Controllers\Api\LevelClassController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\ReligionController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\RfidTapController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SchoolYearsController;
use App\Http\Controllers\Api\SemesterController;
use App\Http\Controllers\Api\Student\StudentAttendancePermissionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\Student\StudentLessonScheduleController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
| Login & Logout endpoints
*/
Route::controller(LoginController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| School Operator (Admin)
|--------------------------------------------------------------------------
| Semua endpoint yang hanya boleh diakses operator sekolah
*/
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
    Route::apiResource('level-classes', LevelClassController::class)->only(['index', 'show']);
    // Classrooms
    Route::apiResource('classrooms', ClassroomController::class);
    // Classroom Students
    Route::prefix('classroom-students')->controller(ClassroomStudentsController::class)->group(function () {
        Route::get('{classroomId}/available-students', 'getAvailableStudents');
        Route::post('{classroomId}/add-students', 'addStudents');
        Route::delete('{classroomId}/remove-student/{studentId}', 'removeStudent');
        Route::get('{classroomId}/active-students', 'getActiveStudents');
    });
    Route::apiResource('classroom-students', ClassroomStudentsController::class);
    // School Years
    Route::prefix('school-years')->controller(SchoolYearsController::class)->group(function () {
        Route::post('{id}/activate', 'activate'); // aktifkan tahun ajaran
        Route::get('active', 'active'); // tahun ajaran aktif
    });
    Route::apiResource('school-years', SchoolYearsController::class)->except(['show','update']);
    // Semesters
    Route::prefix('semesters')->controller(SemesterController::class)->group(function () {
        Route::get('active', 'active'); // semester aktif
    });
    // Subjects
    Route::apiResource('subjects', SubjectController::class);
    // Lesson Hours
    Route::prefix('lesson-hours')->controller(LessonHourController::class)->group(function () {
        Route::get('grouped/days', 'getAllGroupedByDay'); // jam pelajaran dikelompokkan berdasarkan hari
        Route::get('day/{day}', 'getByDay'); // jam pelajaran khusus hari tertentu
    });
    Route::apiResource('lesson-hours', LessonHourController::class)->except(['update']);

    // Lesson Schedules
    Route::prefix('lesson-schedules')->controller(LessonSchedulesController::class)->group(function () {
        Route::get('{classroomId}/schedules/{day}', 'getByClassroomAndDay'); // jadwal kelas per hari
        Route::get('{classroomId}/schedules', 'getByClassroom'); // jadwal lengkap per kelas
    });
    Route::apiResource('lesson-schedules', LessonSchedulesController::class);
    // Attendance Rules
    Route::prefix('attendance-rules')->controller(AttendanceRuleController::class)->group(function () {
        Route::post('day/{day}', 'updateByDay'); // update aturan absensi per hari
        Route::get('day/{day}', 'getByDay'); // aturan absensi hari tertentu
    });
    Route::apiResource('attendance-rules', AttendanceRuleController::class)->only(['index','store']);
    // RFID Management
    Route::prefix('rfids')->controller(RfidController::class)->group(function () {
        Route::get('available-students', 'availableStudents');  // list siswa yg belum punya kartu RFID
    });
    Route::apiResource('rfids', RfidController::class);
    // RFID Tap (perlu Master card)

    Route::post('rfid-tap', [RfidTapController::class, 'tap']); 
// });

/*
|--------------------------------------------------------------------------
| Student Routes
|--------------------------------------------------------------------------
| Akses hanya untuk siswa
*/
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {

    // jadwal pelajaran siswa
    Route::get('/lesson-schedule', [StudentLessonScheduleController::class, 'getSchedule']);
    // izin tidak masuk siswa
    Route::apiResource('attendance-permissions', StudentAttendancePermissionController::class)->except(['update']);
});

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
| Akses hanya untuk guru
*/
// Route::middleware(['auth:sanctum', 'role:teacher'])->prefix('teacher')->group(function () {

//     // absensi croscheck
//     Route::prefix('attendance')->controller(TeacherAttendanceController::class)->group(function () {
//         Route::get('/cross-check-data', 'getCrossCheckData');
//         Route::post('/cross-check', 'submitCrossCheck');
//         Route::get('/schedule', 'getTeacherSchedule');
//         Route::get('/classroom-summary', 'getClassroomSummary');
//     });
//     // CRUD attendance
// });

/*
|--------------------------------------------------------------------------
| Counselor Routes
|--------------------------------------------------------------------------
| Akses hanya untuk guru BK
*/
Route::middleware(['auth:sanctum', 'role:counselor'])->prefix('counselor')->group(function () {

    // BK validasi izin
    Route::prefix('attendance-permissions')->controller(CounselorAttendancePermissionController::class)->group(function () {
        Route::get('pending', 'pending');   // list izin yg belum divalidasi
        Route::post('{id}/approve', 'approve'); // setujui izin
        Route::post('{id}/reject', 'reject');   // tolak izin
    });
    Route::apiResource('attendance-permissions', CounselorAttendancePermissionController::class)->except(['store', 'destroy']);
});

// Route::middleware(['auth:sanctum', 'role:homeroom_teacher'])->group(function () {
    // Tambahkan routes untuk homeroom teacher di sini
// });

// Route::middleware(['auth:sanctum', 'role:curriculum_coordinator'])->group(function () {
    // Tambahkan routes untuk curriculum coordinator di sini
// });