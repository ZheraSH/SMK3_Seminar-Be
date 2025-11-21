<?php

use App\Http\Controllers\Api\AttendanceController;
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
use App\Http\Controllers\Api\StudentLessonScheduleController;
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
    Route::prefix('classrooms')->controller(ClassroomController::class)->group(function () {
        Route::get('{classroom}/available-students', 'getAvailableStudents'); // daftar siswa yg bisa dimasukkan ke kelas
        Route::post('{classroom}/add-students', 'addStudents'); // tambah siswa ke kelas
        Route::delete('{classroom}/remove-student/{studentId}', 'removeStudent'); // hapus siswa dari kelas
    });
    Route::apiResource('classrooms', ClassroomController::class);
    // Classroom Students
    Route::apiResource('classroom-students', ClassroomStudentsController::class)->only('index');
    // School Years
    Route::prefix('school-years')->controller(SchoolYearsController::class)->group(function () {
        Route::patch('{id}/activate', 'activate'); // aktifkan tahun ajaran
        Route::get('active', 'active'); // tahun ajaran aktif
    });
    Route::apiResource('school-years', SchoolYearsController::class)->except(['update']);
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
    Route::prefix('lesson-schedules')->controller(StudentLessonScheduleController::class)->group(function () {
          Route::get('{day}', 'getByDay'); // Filter berdasarkan hari (senin, selasa, rabu, kamis, jumat)
    });
    Route::apiResource('lesson-schedules', StudentLessonScheduleController::class)->only(['index']);
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
//     Route::prefix('attendances')->controller(AttendanceController::class)->group(function () {
//         Route::get('classroom/{classroomId}', 'getByClassroom'); // absensi seluruh kelas
//         Route::get('student/{studentId}/monthly', 'getStudentMonthly'); // rekap absensi bulanan per siswa
//         Route::get('student/{studentId}/today', 'getTodayByStudent'); // absensi siswa hari ini
//         Route::get('by-date', 'getByDate'); // absensi berdasarkan tanggal
//     });
//     Route::apiResource('attendances', AttendanceController::class);
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