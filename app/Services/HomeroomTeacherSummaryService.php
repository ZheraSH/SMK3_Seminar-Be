<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Contracts\Repositories\ClassroomRepository;
use App\Enums\AttendanceStatusEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Carbon\Carbon;

class HomeroomTeacherSummaryService
{
    protected ClassroomRepository $classroomRepository;
    protected ClassroomStudentsInterface $classroomStudentsInterface;
    protected AttendanceInterface $attendanceInterface;
    protected AttendancePermissionInterface $permissionInterface;

    public function __construct(
        ClassroomRepository $classroomRepository,
        ClassroomStudentsInterface $classroomStudentsInterface,
        AttendanceInterface $attendanceInterface,
        AttendancePermissionInterface $permissionInterface
    ) {
        $this->classroomRepository = $classroomRepository;
        $this->classroomStudentsInterface = $classroomStudentsInterface;
        $this->attendanceInterface = $attendanceInterface;
        $this->permissionInterface = $permissionInterface;
    }

    public function getTeacherClassroom(User $teacher): ?array
    {
        if (!$this->isHomeroomTeacher($teacher)) {
            return null;
        }

        if (!$teacher->relationLoaded('employee')) {
            $teacher->load('employee');
        }

        if (!$teacher->employee) {
            return null;
        }

        $classroom = $this->classroomRepository->getModel()
            ->where('teacher_id', $teacher->employee->id)
            ->with(['major', 'levelClass', 'schoolYear'])
            ->first();

        if (!$classroom) {
            return null;
        }

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'major' => $classroom->major?->name,
            'level_class' => $classroom->levelClass?->name,
            'school_year' => $classroom->schoolYear?->name,
            'total_students' => $this->classroomStudentsInterface->countActiveByClassroom($classroom->id),
        ];
    }

    public function getDailySummary(User $teacher, string $date): array
    {
        $classroom = $this->getTeacherClassroom($teacher);
        
        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        $totalStudents = $classroom['total_students'];
        
        if ($totalStudents === 0) {
            return $this->emptyDailySummary($date);
        }

        $attendances = $this->attendanceInterface->getByClassroomAndDate($classroom['id'], $date);

        $morningAttendances = $attendances->filter(fn($att) => $att->lesson_order == 1);

        $permissions = $this->getApprovedPermissions($classroom['id'], $date);

        $counters = [
            'present' => 0,
            'late' => 0,
            'sick' => 0,
            'permission' => 0,
            'alpha' => 0,
        ];

        $this->processStudentsAttendance($counters, $classroom['id'], $date, $morningAttendances, $permissions);

        $attended = $counters['present'] + $counters['late'] + $counters['sick'] + $counters['permission'];
        $percentage = $totalStudents > 0 ? round(($attended / $totalStudents) * 100, 2) : 0;

        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->translatedFormat('l'),
            'classroom_id' => $classroom['id'],
            'classroom_name' => $classroom['name'],
            'total_students' => $totalStudents,
            'present' => $counters['present'],
            'late' => $counters['late'],
            'sick' => $counters['sick'],
            'permission' => $counters['permission'],
            'alpha' => $counters['alpha'],
            'percentage' => $percentage,
        ];
    }

    public function getWeeklyStatistics(User $teacher, string $startDate, string $endDate): array
    {
        $classroom = $this->getTeacherClassroom($teacher);
        
        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalStudents = $classroom['total_students'];

        $weeklyData = [];
        $currentDate = $start->copy();

        while ($currentDate->lte($end)) {
            $dateString = $currentDate->format('Y-m-d');

            $attendances = $this->attendanceInterface->getByClassroomAndDate($classroom['id'], $dateString);
            $morningAttendances = $attendances->filter(fn($att) => $att->lesson_order == 1);
            $permissions = $this->getApprovedPermissions($classroom['id'], $dateString);
            $dailyCount = $this->countDailyAttendance($classroom['id'], $dateString, $morningAttendances, $permissions);

            $weeklyData[] = [
                'date' => $dateString,
                'day_name' => $currentDate->translatedFormat('l'),
                'day_short' => $currentDate->translatedFormat('D'),
                'total_students' => $totalStudents,
                'hadir' => $dailyCount['present'] + $dailyCount['late'],
                'izin' => $dailyCount['permission'],
                'alpha' => $dailyCount['alpha'],
                'telat' => $dailyCount['late'],
                'sakit' => $dailyCount['sick'],
            ];

            $currentDate->addDay();
        }

        $totals = [
            'total_days' => count($weeklyData),
            'total_hadir' => array_sum(array_column($weeklyData, 'hadir')),
            'total_izin' => array_sum(array_column($weeklyData, 'izin')),
            'total_alpha' => array_sum(array_column($weeklyData, 'alpha')),
            'total_telat' => array_sum(array_column($weeklyData, 'telat')),
            'total_sakit' => array_sum(array_column($weeklyData, 'sakit')),
        ];

        return [
            'period' => [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ],
            'classroom' => [
                'id' => $classroom['id'],
                'name' => $classroom['name'],
                'total_students' => $totalStudents,
            ],
            'totals' => $totals,
            'daily_data' => $weeklyData,
        ];
    }

    public function getDailyAttendance(User $teacher, string $date, int $perPage = 10): array
    {
        $classroom = $this->getTeacherClassroom($teacher);
        
        if (!$classroom) {
            throw new \Exception('Anda tidak memiliki kelas sebagai wali kelas', 404);
        }

        $students = $this->classroomStudentsInterface->getByClassroomForAttendance($classroom['id']);
        $attendances = $this->attendanceInterface->getByClassroomAndDate($classroom['id'], $date);
        $morningAttendances = $attendances->filter(fn($att) => $att->lesson_order == 1);
        $permissions = $this->getApprovedPermissions($classroom['id'], $date);

        $attendanceData = $students->getCollection()->map(function ($classroomStudent) use ($date, $morningAttendances, $permissions) {
            if (!$classroomStudent->student) {
                return null;
            }

            $studentId = $classroomStudent->student_id;
            $student = $classroomStudent->student;

            $statusData = $this->determineStudentStatus($studentId, $date, $morningAttendances, $permissions);

            return [
                'student_uuid' => $student->id,
                'student_name' => $student->user->name ?? 'Unknown',
                'nisn' => $student->nisn,
                'status' => $statusData['status'],
                'time_in' => $statusData['time_in'],
                'time_out' => $statusData['time_out'],
                'date' => $date,
            ];
        })->filter();

        $summary = $this->getDailySummary($teacher, $date);

        return [
            'summary' => $summary,
            'students' => $attendanceData,
            'pagination' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ];
    }

    private function isHomeroomTeacher(User $user): bool
    {
        return $user->roles->contains(function ($role) {
            return $role->name === RoleEnum::HOMEROOM_TEACHER->value;
        });
    }

    private function getApprovedPermissions(string $classroomId, string $date)
    {
        $dateObj = Carbon::parse($date);

        $allPermissions = \App\Models\AttendancePermission::where('status', 'approved')
            ->whereDate('start_date', '<=', $dateObj)
            ->whereDate('end_date', '>=', $dateObj)
            ->with(['student'])
            ->get();

        if ($allPermissions->isEmpty()) {
            return collect();
        }

        // Filter by students in this classroom
        $studentIds = $allPermissions->pluck('student_id');
        
        $classroomStudentIds = \App\Models\ClassroomStudents::where('classroom_id', $classroomId)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id');

        return $allPermissions->filter(function ($permission) use ($classroomStudentIds) {
            return $classroomStudentIds->contains($permission->student_id);
        });
    }

    private function processStudentsAttendance(array &$counters, string $classroomId, string $date, $morningAttendances, $permissions): void
    {
        $classroomStudents = \App\Models\ClassroomStudents::where('classroom_id', $classroomId)
            ->where('status', 'active')
            ->with('student')
            ->get();

        foreach ($classroomStudents as $cs) {
            if (!$cs->student) continue;

            $studentId = $cs->student_id;

            $hasPermission = $permissions->contains(function ($permission) use ($studentId) {
                return $permission->student_id === $studentId;
            });

            if ($hasPermission) {
                $counters['permission']++;
                continue;
            }

            $attendance = $morningAttendances->firstWhere('student_id', $studentId);

            if ($attendance) {
                switch ($attendance->status) {
                    case AttendanceStatusEnum::PRESENT->value:
                        $counters['present']++;
                        break;
                    case AttendanceStatusEnum::LATE->value:
                        $counters['late']++;
                        break;
                    case AttendanceStatusEnum::SICK->value:
                        $counters['sick']++;
                        break;
                    case AttendanceStatusEnum::ALPHA->value:
                        $counters['alpha']++;
                        break;
                }
            } else {
                $counters['alpha']++;
            }
        }
    }

    private function countDailyAttendance(string $classroomId, string $date, $morningAttendances, $permissions): array
    {
        $counters = [
            'present' => 0,
            'late' => 0,
            'sick' => 0,
            'permission' => 0,
            'alpha' => 0,
        ];

        $this->processStudentsAttendance($counters, $classroomId, $date, $morningAttendances, $permissions);
        
        return $counters;
    }

    private function determineStudentStatus(string $studentId, string $date, $morningAttendances, $permissions): array
    {
        $hasPermission = $permissions->contains(function ($permission) use ($studentId) {
            return $permission->student_id === $studentId;
        });

        if ($hasPermission) {
            return [
                'status' => 'permission',
                'time_in' => null,
                'time_out' => null,
            ];
        }

        $attendance = $morningAttendances->firstWhere('student_id', $studentId);
        
        if ($attendance) {
            return [
                'status' => $attendance->status,
                'time_in' => $attendance->checkin_time,
                'time_out' => $attendance->checkout_time,
            ];
        }

        return [
            'status' => 'alpha',
            'time_in' => null,
            'time_out' => null,
        ];
    }

    private function emptyDailySummary(string $date): array
    {
        return [
            'date' => $date,
            'day_name' => Carbon::parse($date)->translatedFormat('l'),
            'classroom_id' => null,
            'classroom_name' => null,
            'total_students' => 0,
            'present' => 0,
            'late' => 0,
            'sick' => 0,
            'permission' => 0,
            'alpha' => 0,
            'percentage' => 0,
        ];
    }
}