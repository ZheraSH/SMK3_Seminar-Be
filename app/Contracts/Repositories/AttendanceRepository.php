<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\AttendanceProofEnum;
use Illuminate\Support\Facades\DB;

class AttendanceRepository extends BaseRepository implements AttendanceInterface
{
    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    public function get(): Collection
    {
        return $this->model->query()->latest()->get();
    }

    public function store(array $data): Attendance
    {
        return $this->model->query()->create($data);
    }

    public function storeBulk(array $data): bool
    {
        return $this->model->query()->insert($data);
    }

    public function show($id): Attendance
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function updateOrInsert(array $attributes, array $data): mixed
    {
        $query = $this->model->query();

        foreach ($attributes as $key => $value) {
            if ($key === 'created_at' || $key === 'updated_at') {
                $query->whereDate($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        $record = $query->first();

        if ($record) {
            $record->update($data);
            return $record;
        }

        unset($attributes['created_at'], $attributes['updated_at']);

        return $this->model->query()
            ->create($data);
    }

    public function delete($id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function search($request): Collection
    {
        return $this->model->where('name', 'LIKE', '%' . $request->search . '%')->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model->query()
            ->whereDate('date', $date)
            ->latest()
            ->get();
    }

    //Student
    public function getStudentHistory(string $studentId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('attendance_type', 'rfid')
            ->final()
            ->selectRaw('
                date,
                MIN(checkin_time) as checkin_time,
                MAX(checkout_time) as checkout_time,
                MIN(status) as status
            ')
            ->groupBy('date')
            ->orderByDesc('date')
            ->paginate($perPage);
    }

    public function getStudentSummary(string $studentId): array
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('attendance_type', 'rfid')
            ->final()
            ->selectRaw("
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as alpha
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::ALPHA->value,
            ])
            ->first()
            ->toArray();
    }

    public function getStudentMonthlyStatistic(string $studentId, int $year): array
    {
        $data = $this->model
            ->where('student_id', $studentId)
            ->where('attendance_type', 'rfid')
            ->whereYear('date', $year)
            ->final()
            ->selectRaw("
                MONTH(date) as month,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as alpha
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::ALPHA->value,
            ])
            ->groupByRaw('MONTH(date)')
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($data) {
            return [
                'month' => $month,
                'hadir' => $data[$month]->hadir ?? 0,
                'telat' => $data[$month]->telat ?? 0,
                'alpha' => $data[$month]->alpha ?? 0,
            ];
        })->values()->toArray();
    }

    //Student close

    //Teacher
    public function getByScheduleAndDate(string $lessonScheduleId, string $date): Collection
    {
        return $this->model
            ->where('lesson_schedule_id', $lessonScheduleId)
            ->whereDate('date', $date)
            ->get();
    }

    public function getByStudentLesson(string $studentId, string $date, int $lessonOrder): ?Attendance
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->first();
    }

    public function getByClassroomAndDate(string $classroomId, string $date): Collection
    {
        return $this->model
            ->whereHas('student.classroomStudents', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)
                    ->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value);
            })
            ->whereDate('date', $date)
            ->final()
            ->get();
    }

    public function getDailyInitialAttendances(string $date, string $classroomId = null): Collection
    {
        $query = $this->model
            ->whereDate('date', $date)
            ->where('attendance_type', 'initial');

        if ($classroomId) {
            $query->whereHas('classroomStudent', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        return $query->get();
    }

    public function getRFIDAttendanceByStudentAndDate(string $studentId, string $date): ?Attendance
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('attendance_type', 'rfid')
            ->first();
    }

    public function getFinalAttendancesForStudent(string $studentId, string $date): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->orderBy('lesson_order')
            ->get();
    }

    public function lockAttendance(string $studentId, string $date, int $lessonOrder, string $status, string $permissionId, ?string $lessonScheduleId = null, ?string $subjectId = null, ?string $teacherId = null, ?string $classroomStudentId = null): Attendance
    {
        $data = [
            'status' => $status,
            'is_locked' => true,
            'overridden_by_permission_id' => $permissionId,
            'is_final' => true,
            'proof' => AttendanceProofEnum::PERMISSION->value
        ];

        if ($lessonScheduleId) $data['lesson_schedule_id'] = $lessonScheduleId;
        if ($subjectId) $data['subject_id'] = $subjectId;
        if ($teacherId) $data['teacher_id'] = $teacherId;
        if ($classroomStudentId) $data['classroom_student_id'] = $classroomStudentId;

        return $this->model->updateOrCreate(
            [
                'student_id' => $studentId,
                'date' => $date,
                'lesson_order' => $lessonOrder
            ],
            $data
        );
    }

    public function isAttendanceLocked(string $studentId, string $date, int $lessonOrder): bool
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->where('is_locked', true)
            ->exists();
    }

    public function getSubmissionInfo(string $lessonScheduleId, string $date, int $lessonOrder): ?Attendance
    {
        return $this->model
            ->where('lesson_schedule_id', $lessonScheduleId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->where('attendance_type', 'cross_check')
            ->orderByDesc('updated_at')
            ->first();
    }

    //Teacher Close

    // Counselor
    public function countTotalStatusToday(string $date): array
    {
        return $this->model
            ->whereDate('date', $date)
            ->where('attendance_type', 'rfid')
            ->final()
            ->selectRaw("
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as hadir,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as telat,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as izin, 
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as sakit,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as alpha
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::SICK->value,
                AttendanceStatusEnum::ALPHA->value
            ])
            ->first()
            ->toArray();
    }

    public function getStudentsWithHighAlpha(int $threshold, int $limit = 10): Collection
    {
        return $this->model
            ->where('status', AttendanceStatusEnum::ALPHA->value)
            ->final()
            ->selectRaw('student_id, COUNT(*) as total_alpha')
            ->groupBy('student_id')
            ->having('total_alpha', '>=', $threshold)
            ->orderByDesc('total_alpha')
            ->limit($limit)
            ->with(['student.classroomStudents' => function ($q) {
                $q->where('status', 'active')->with('classroom');
            }])
            ->get();
    }
    public function countTotalStatusGlobal(): array
    {
        $data = $this->model->query()
            ->where('attendance_type', 'rfid')
            ->final()
            ->selectRaw("
                COUNT(CASE WHEN status = ? THEN 1 END) as hadir,
                COUNT(CASE WHEN status = ? THEN 1 END) as terlambat,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as izin,
                COUNT(CASE WHEN status = ? THEN 1 END) as alpha,
                COUNT(*) as total
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::SICK->value,
                AttendanceStatusEnum::ALPHA->value
            ])
            ->first();

        return $data ? $data->toArray() : [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'alpha' => 0,
            'total' => 0
        ];
    }

    public function getMonthlyAttendanceSummaryPerStudent(int $month, int $year): Collection
    {
        return $this->model->query()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('attendance_type', 'rfid')
            ->final()
            ->select('student_id')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir", [AttendanceStatusEnum::PRESENT->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat", [AttendanceStatusEnum::LATE->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as izin", [AttendanceStatusEnum::LEAVE->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sakit", [AttendanceStatusEnum::SICK->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as alpha", [AttendanceStatusEnum::ALPHA->value])
            ->groupBy('student_id')
            ->get();
    }

    public function countTotalStatusMonthly(int $year): array
    {
        $data = $this->model->query()
            ->whereYear('date', $year)
            ->where('attendance_type', 'rfid')
            ->final()
            ->selectRaw("
                MONTH(date) as month,
                COUNT(CASE WHEN status = ? THEN 1 END) as hadir,
                COUNT(CASE WHEN status = ? THEN 1 END) as terlambat,
                COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as izin,
                COUNT(CASE WHEN status = ? THEN 1 END) as alpha
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::SICK->value,
                AttendanceStatusEnum::ALPHA->value
            ])
            ->groupByRaw('MONTH(date)')
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($data) {
            return [
                'month' => $month,
                'hadir' => $data[$month]->hadir ?? 0,
                'terlambat' => $data[$month]->terlambat ?? 0,
                'izin' => $data[$month]->izin ?? 0,
                'alpha' => $data[$month]->alpha ?? 0,
            ];
        })->values()->toArray();
    }
    // Counselor Close

    // Homeroom Teacher
    public function getRFIDLogByClassroom(string $classroomId, string $date): Collection
    {
        return $this->model
            ->whereDate('date', $date)
            ->where('attendance_type', 'rfid')
            ->final()
            ->whereHas('student.classroomStudents', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)
                    ->where('status', 'active');
            })
            ->with(['student.user'])
            ->orderBy('checkin_time', 'desc')
            ->get();
    }
    // Homeroom Teacher Close

    // Operator
    public function getRecentRfidActivities(int $limit = 10)
    {
        return DB::table('attendances')
            ->join('students', 'attendances.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classroom_students', function ($join) {
                $join->on('students.id', '=', 'classroom_students.student_id')
                    ->where('classroom_students.status', '=', 'active');
            })
            ->leftJoin('classrooms', 'classroom_students.classroom_id', '=', 'classrooms.id')
            ->whereDate('attendances.date', now())
            ->where('attendances.attendance_type', 'rfid')
            ->where('attendances.is_final', true)
            ->groupBy('attendances.student_id', 'users.name', 'classrooms.name', 'attendances.status', 'attendances.checkin_time')
            ->orderByDesc('attendances.checkin_time')
            ->limit($limit)
            ->get([
                DB::raw('MAX(attendances.id) as id'),
                'users.name',
                'classrooms.name as classroom',
                'attendances.status',
                'attendances.checkin_time',
            ]);
    }

    public function getTodayAttendanceSummary()
    {
        // For today's summary, we rely on the primary RFID record per student
        return DB::table('attendances')
            ->whereDate('date', now())
            ->where('attendance_type', 'rfid')
            ->where('is_final', true)
            ->selectRaw('
                 COUNT(CASE WHEN status = ? THEN 1 END) as present,
                 COUNT(CASE WHEN status = ? THEN 1 END) as late,
                 COUNT(CASE WHEN status IN (?, ?) THEN 1 END) as permission,
                 COUNT(CASE WHEN status = ? THEN 1 END) as alpha
             ', [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::SICK->value,
                AttendanceStatusEnum::LEAVE->value,
                AttendanceStatusEnum::ALPHA->value,
            ])
            ->first();
    }

    public function getMonthlyAttendanceChart(int $year)
    {
        return DB::table('attendances')
            ->selectRaw('
                 MONTH(date) as month,
                 COUNT(DISTINCT CASE WHEN status IN (?, ?) THEN DATE(date) END) as total_days_with_attendance,
                 COUNT(DISTINCT CASE WHEN status IN (?, ?) THEN CONCAT(student_id, DATE(date)) END) as total_present
             ', [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->whereYear('date', $year)
            ->where('attendance_type', 'rfid')
            ->where('is_final', true)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    //Operator Close
}
