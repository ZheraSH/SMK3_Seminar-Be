<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;

class OperatorDashboardService
{
    private StudentRepository $studentRepository;
    private EmployeeRepository $employeeRepository;
    private ClassroomRepository $classroomRepository;
    private MajorRepository $majorRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;

    public function __construct(StudentRepository $studentRepository, EmployeeRepository $employeeRepository, ClassroomRepository $classroomRepository, MajorRepository $majorRepository, AttendanceRfidRepository $attendanceRfidRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->classroomRepository = $classroomRepository;
        $this->majorRepository = $majorRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
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
        return $this->attendanceRfidRepository->getRecentActivities($limit);
    }

    public function getTodayAttendanceSummary(): array
    {
        $totalStudents = $this->studentRepository->count();

        $data = $this->attendanceRfidRepository->getTodaySummary();

        return [
            'total_students' => $totalStudents,
            'present' => (int) $data->present,
            'late' => (int) $data->late,
        ];
    }

    public function getMonthlyAttendanceChart()
    {
        $totalStudents = $this->studentRepository->count();

        $monthlyData = $this->attendanceRfidRepository->getMonthlyChart(now()->year);

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
