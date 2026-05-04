<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Contracts\Repositories\AttendanceRepository;

class OperatorDashboardService
{
    private StudentRepository $studentRepository;
    private EmployeeRepository $employeeRepository;
    private ClassroomRepository $classroomRepository;
    private MajorRepository $majorRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(StudentRepository $studentRepository, EmployeeRepository $employeeRepository, ClassroomRepository $classroomRepository, MajorRepository $majorRepository, AttendanceRepository $attendanceRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->classroomRepository = $classroomRepository;
        $this->majorRepository = $majorRepository;
        $this->attendanceRepository = $attendanceRepository;
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

    public function getRfidActivities($request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        $classroomName = $request->get('classroom_id');

        return $this->attendanceRepository->getRecentRfidActivities($perPage, $search, $classroomName);
    }

    public function getTodayAttendanceSummary(): array
    {
        $totalStudents = $this->studentRepository->count();

        $data = $this->attendanceRepository->getTodayAttendanceSummary();

        return [
            'total_students' => $totalStudents,
            'present' => (int) $data->present,
            'late' => (int) $data->late,
            'permission' => (int) $data->permission,
            'absent' => (int) $data->alpha,
        ];
    }

    public function getMonthlyAttendanceChart()
    {
        $totalStudents = $this->studentRepository->count();

        $monthlyData = $this->attendanceRepository->getMonthlyAttendanceChart(now()->year);

        return $monthlyData->map(function ($row) use ($totalStudents) {
            $schoolDays = $row->total_days_with_attendance ?: 20;
            $maxPossible = $totalStudents * $schoolDays;

            return [
                'month' => $row->month,
                'percentage' => $maxPossible
                    ? round(($row->total_present / $maxPossible) * 100, 2)
                    : 0,
            ];
        });
    }
}
