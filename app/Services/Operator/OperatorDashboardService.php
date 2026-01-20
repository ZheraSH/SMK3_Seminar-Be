<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Contracts\Repositories\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OperatorDashboardService
{
    private StudentRepository $studentRepository;
    private EmployeeRepository $employeeRepository;
    private ClassroomRepository $classroomRepository;
    private AttendanceRepository $attendanceRepository;
    private MajorRepository $majorRepository;

    public function __construct(
        StudentRepository $studentRepository,
        EmployeeRepository $employeeRepository,
        ClassroomRepository $classroomRepository,
        AttendanceRepository $attendanceRepository,
        MajorRepository $majorRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->classroomRepository = $classroomRepository;
        $this->attendanceRepository = $attendanceRepository;
        $this->majorRepository = $majorRepository;
    }

    public function getMaster(): array
    {
        $totalStudents = $this->studentRepository->count();
        $totalEmployees = $this->employeeRepository->count();
        $totalClassrooms = $this->classroomRepository->countActive();
        $totalMajors = $this->majorRepository->count();

        return [
            'total_students' => $totalStudents,
            'total_employees' => $totalEmployees,
            'total_classrooms' => $totalClassrooms,
            'total_majors' => $totalMajors,
        ];
    }

    public function getRfidActivities(int $limit = 10)
    {
        return DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classroom_students', 'students.id', '=', 'classroom_students.student_id')
            ->leftJoin('classrooms', 'classroom_students.classroom_id', '=', 'classrooms.id')
            ->whereDate('attendances.date', now())
            ->whereNotNull('attendances.rfid_id')
            ->orderByDesc('attendances.checkin_time')
            ->limit($limit)
            ->get([
                'attendances.id',
                'users.name',
                'classrooms.name as classroom',
                'attendances.status',
                'attendances.checkin_time',
            ]);
    }

    public function getTodayAttendanceSummary(): array
    {
        $totalStudents = $this->studentRepository->count();

        $data = DB::table('attendances')
            ->whereDate('date', now())
            ->selectRaw('
                 SUM(status = "present")    as present,
                 SUM(status = "late")       as late,
                 SUM(status = "permission") as permission,
                 SUM(status = "absent")     as absent
             ')
            ->first();

        return [
            'total_students' => $totalStudents,
            'present' => (int) $data->present,
            'late' => (int) $data->late,
            'permission' => (int) $data->permission,
            'absent' => (int) $data->absent,
        ];
    }

    public function getMonthlyAttendanceChart()
    {
        $totalStudents = $this->studentRepository->count();

        return DB::table('attendances')
            ->selectRaw('
                 MONTH(date) as month,
                 COUNT(*) as total_records,
                 SUM(status IN ("present", "late")) as total_present
             ')
            ->whereYear('date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($row) use ($totalStudents) {
                $maxAttendance = $totalStudents * Carbon::create()->month($row->month)->daysInMonth;

                return [
                    'month' => $row->month,
                    'percentage' => $maxAttendance
                        ? round(($row->total_present / $maxAttendance) * 100, 2)
                        : 0,
                ];
            });
    }
}
