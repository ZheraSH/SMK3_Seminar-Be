<?php

namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\StudentRepository;
use App\Contracts\Repositories\Operator\EmployeeRepository;
use App\Contracts\Repositories\Operator\ClassroomRepository;
use App\Contracts\Repositories\Operator\MajorRepository;
use App\Contracts\Repositories\AttendanceRfidRepository;
use App\Contracts\Repositories\AttendanceRepository;

class OperatorDashboardService
{
    private StudentRepository $studentRepository;
    private EmployeeRepository $employeeRepository;
    private ClassroomRepository $classroomRepository;
    private MajorRepository $majorRepository;
    private AttendanceRfidRepository $attendanceRfidRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(StudentRepository $studentRepository, EmployeeRepository $employeeRepository, ClassroomRepository $classroomRepository, MajorRepository $majorRepository, AttendanceRfidRepository $attendanceRfidRepository, AttendanceRepository $attendanceRepository)
    {
        $this->studentRepository = $studentRepository;
        $this->employeeRepository = $employeeRepository;
        $this->classroomRepository = $classroomRepository;
        $this->majorRepository = $majorRepository;
        $this->attendanceRfidRepository = $attendanceRfidRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function getCounter(): array
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

    public function getMonthlyAttendanceChart()
    {
        $totalStudents = $this->studentRepository->count();

        $monthlyData = $this->attendanceRfidRepository->getMonthlyChart(now()->year)->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($totalStudents, $monthlyData) {
            $row = $monthlyData->get($month);

            if (!$row) {
                return [
                    'month' => $month,
                    'percentage' => 0,
                ];
            }

            $schoolDays = $row->total_days_with_attendance ?: 20;
            $maxPossible = $totalStudents * $schoolDays;

            return [
                'month' => $month,
                'percentage' => $maxPossible
                    ? round(($row->total_present / $maxPossible) * 100, 2)
                    : 0,
            ];
        });
    }

    public function getTodayAttendanceSummary(): array
    {
        $totalStudents = $this->studentRepository->count();

        $data = $this->attendanceRepository->countTotalStatusToday(now()->format('Y-m-d'));

        $present = (int) ($data['hadir'] ?? 0);
        $sick = (int) ($data['sakit'] ?? 0);
        $permission = (int) ($data['izin'] ?? 0);
        $alpha = (int) ($data['alpha'] ?? 0);

        return [
            'total_students' => $totalStudents,
            'present' => $present,
            'sick' => $sick,
            'permission' => $permission,
            'alpha' => $alpha,
        ];
    }

    public function getRfidActivities(int $limit = 10)
    {
        return $this->attendanceRfidRepository->getRecentActivities($limit);
    }
}
