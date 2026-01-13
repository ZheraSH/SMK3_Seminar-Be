<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Operator\RoleController;
use App\Http\Controllers\APi\Operator\SchoolController;
use App\Http\Controllers\Api\Operator\ReligionController;
use App\Http\Controllers\Api\Operator\EmployeeController;
use App\Http\Controllers\Api\Operator\StudentController;
use App\Http\Controllers\Api\Operator\MajorController;
use App\Http\Controllers\Api\Operator\LevelClassController;
use App\Http\Controllers\Api\Operator\SchoolYearsController;
use App\Http\Controllers\Api\Operator\ClassroomController;
use App\Http\Controllers\Api\Operator\ClassroomStudentsController;
use App\Http\Controllers\Api\Operator\SemesterController;
use App\Http\Controllers\Api\Operator\SubjectController;
use App\Http\Controllers\Api\Operator\LessonHourController;
use App\Http\Controllers\Api\Operator\LessonSchedulesController;
use App\Http\Controllers\Api\Operator\AttendanceRuleController;
use App\Http\Controllers\Api\Operator\RfidController;
use App\Http\Controllers\Api\Operator\RfidTapController;
use App\Http\Controllers\Api\Operator\OperatorDashboardController;
use App\Http\Controllers\Api\Student\StudentsController;
use App\Http\Controllers\Api\Student\StudentDashboardController;
use App\Http\Controllers\Api\Teacher\TeachersController;
// use App\Http\Controllers\Api\Teacher\TeacherDashboardController;
use App\Http\Controllers\Api\Counselor\CounselorsController;
use App\Http\Controllers\Api\Counselor\CounselorDashboardController;
use App\Http\Controllers\Api\Homeroom_teacher\HomeroomTeachersController;
use App\Http\Controllers\Api\Homeroom_teacher\HomeroomTeacherDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    // Public routes
    Route::post('login', 'login');
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', 'logout');
        Route::post('change-password', 'changePassword');
        // Operator-only routes
        Route::post('reset-password', 'resetPassword')->middleware('role:school_operator');
    });
});

/*
|--------------------------------------------------------------------------
| School Operator (Admin)
|--------------------------------------------------------------------------
| Semua endpoint yang hanya boleh diakses operator sekolah
*/
// Route::middleware(['auth:sanctum', 'role:school_operator'])->group(function () {
//Dashboard
Route::prefix('dashboard')->controller(OperatorDashboardController::class)->group(function () {
    Route::get('counters', 'getCounter'); //total siswa/guru/kelas/attendance
    Route::get('tap-history', 'getRfidHistory'); //kegiatan tap RFID terbaru
    Route::get('statistic-today', 'getStatisticsDay'); //statistik absen harian
    Route::get('statistic-monthly', 'getStatisticsMonthly'); //statistik absen bulanan
});
//school Informations
Route::apiResource('school-information', SchoolController::class)->only('index');
Route::prefix('school-information')->controller(SchoolController::class)->group(function () {
    Route::post('update', 'update'); //update pake method post
});
// Roles
Route::apiResource('roles', RoleController::class)->only('index');
// Religions
Route::apiResource('religions', ReligionController::class)->only('index');
// Employees
Route::apiResource('employees', EmployeeController::class);
// Students
Route::apiResource('students', StudentController::class);
// Majors
Route::apiResource('majors', MajorController::class)->only('index');
// Level Classes
Route::apiResource('level-classes', LevelClassController::class)->only('index');
// School Years
Route::prefix('school-years')->controller(SchoolYearsController::class)->group(function () {
    Route::get('active', 'active'); // tahun ajaran aktif
    Route::post('{id}/activate', 'activate'); // aktifkan tahun ajaran
});
Route::apiResource('school-years', SchoolYearsController::class)->except(['show', 'update']);
// Classrooms
Route::apiResource('classrooms', ClassroomController::class)->except(['update', 'destroy']);
// Classroom Students
Route::prefix('classrooms/{classroomId}')->controller(ClassroomStudentsController::class)->group(function () {
    Route::get('students', 'index'); // list siswa di classroom (paginate dengan search)
    Route::get('students-available', 'getAvailableStudents'); // siswa belum/bisa pindah dengan search
    Route::post('students-add', 'store'); // tambahkan siswa ke classroom
    Route::delete('student-remove/{studentId}', 'destroy'); // hapus siswa dari classroom
});
// Semesters
Route::prefix('semesters')->controller(SemesterController::class)->group(function () {
    Route::get('active', 'active'); // semester aktif
});
// Subjects
Route::apiResource('subjects', SubjectController::class);
// Lesson Hours
Route::prefix('lesson-hours')->controller(LessonHourController::class)->group(function () {
    Route::get('{day}', 'getByDay'); // jam pelajaran khusus hari tertentu
});
Route::apiResource('lesson-hours', LessonHourController::class)->except(['index', 'show']);
// Lesson Schedules
Route::prefix('lesson-schedules')->controller(LessonSchedulesController::class)->group(function () {
    Route::get('{classroomId}/schedules/{day}', 'getLessonScheduleClassroomAndDay'); // jadwal kelas per hari
});
Route::apiResource('lesson-schedules', LessonSchedulesController::class)->except(['index', 'show']);
// Attendance Rules
Route::prefix('attendance-rules')->controller(AttendanceRuleController::class)->group(function () {
    Route::put('{day}', 'updateByDay'); // update aturan absensi per hari
    Route::get('{day}', 'getByDay'); // aturan absensi hari tertentu
});
Route::apiResource('attendance-rules', AttendanceRuleController::class)->only(['store']);
// RFID Management
Route::prefix('rfids')->controller(RfidController::class)->group(function () {
    Route::get('students-available', 'getAvailableStudents'); // list siswa yg belum punya kartu RFID
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
    //Student Dashboard
    Route::prefix('dashboard')->controller(StudentDashboardController::class)->group(function () {
        Route::get('attendance-summary', 'attendanceSummary');
        Route::get('attendance-monthly', 'attendanceMonthly');
    });
    Route::get('classroom-info', [StudentsController::class, 'getStudentClassroom']); // Kelas Siswa
    Route::get('attendance-history', [StudentsController::class, 'getStudentHistory']); // History Absensi Rfid Siswa
    Route::get('lesson-schedule/{day}', [StudentsController::class, 'getStudentSchedule']); // Jadwal Pelajaran Siswa
    // izin siswa
    Route::prefix('attendance-permissions')->group(function () {
        Route::get('pending', [StudentsController::class, 'pending']);
    });
    Route::apiResource('attendance-permissions', StudentsController::class)->except(['update']);
});

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
| Akses hanya untuk guru
*/
Route::middleware(['auth:sanctum', 'role:teacher'])->prefix('teacher')->group(function () {
    // Teacher Dashboard
    // Route::prefix('dashboard')->controller(TeacherDashboardController::class)->group(function () {
    // });
    // Teacher schedule
    Route::prefix('schedules')->controller(TeachersController::class)->group(function () {
        Route::get('{day}', 'indexSchedule');
        Route::get('classrooms/{day}', 'indexScheduleClassrooms');
    });
    // Teacher attendance cross-check
    Route::prefix('attendances')->controller(TeachersController::class)->group(function () {
        Route::get('form', 'getAttendanceForm');
        Route::post('submit', 'submitAttendance');
    });
});

/*
|--------------------------------------------------------------------------
| Counselor Routes
|--------------------------------------------------------------------------
| Akses hanya untuk guru BK
*/
Route::middleware(['auth:sanctum', 'role:counselor'])->prefix('counselor')->group(function () {
    // BK Dashboard
    Route::prefix('dashboard')->controller(CounselorDashboardController::class)->group(function () {
        Route::get('attendance-counts', 'index');
        Route::get('high-alpha-students', 'highAlphaStudents');
    });
    // Monitoring Kehadiran Siswa (BK)
    // Route::prefix('attendance-monitoring')->controller(CounselorAttendanceMonitoringController::class) ->group(function ()
    // });
    // Statistik Global (BK)
    // Route::prefix('attendance')->controller(CounselorAttendanceGlobalController::class)->group(function () {
    //     Route::get('statistics', 'index');
    // });
    // validasi izin
    Route::prefix('attendance-permissions')->controller(CounselorsController::class)->group(function () {
        Route::get('pending', 'pending'); // list izin yg belum divalidasi
        Route::post('{id}/approve', 'approve'); // setujui izin
        Route::post('{id}/reject', 'reject'); // tolak izin
    });
    Route::apiResource('attendance-permissions', CounselorsController::class)->except(['store', 'destroy', 'update']);
});

/*
|--------------------------------------------------------------------------
| Homeroom_Teacher Routes
|--------------------------------------------------------------------------
| Akses hanya untuk wali kelas
*/
Route::middleware(['auth:sanctum', 'role:homeroom_teacher'])->prefix('homeroom-teacher')->group(function () {
    //HomeroomTeacher Dashboard
    Route::prefix('dashboard')->controller(HomeroomTeacherDashboardController::class)->group(function () {
        Route::get('attendance-counts', 'indexStats');
        Route::get('rfid-logs', 'rfidLogs');
    });
    // rekap kelas 
    Route::prefix('summary-class')->controller(HomeroomTeachersController::class)->group(function () {
        Route::get('header', 'getHeaderClass'); // Get classroom header (auto today)
        Route::get('students', 'getStudentAttendanceList'); // Student list with search & pagination
        Route::get('recap', 'generateAttendanceRecap'); // Attendance recap for print/export
    });
});

// Route::middleware(['auth:sanctum', 'role:curriculum_coordinator'])->group(function () {
    // Tambahkan routes untuk curriculum coordinator di sini
// });

// Route::middleware(['auth:sanctum', 'role:staff_tu'])->group(function () {
    // Tambahkan routes untuk staff tu di sini
// });