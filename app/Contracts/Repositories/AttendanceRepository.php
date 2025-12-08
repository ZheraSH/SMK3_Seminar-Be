<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository extends BaseRepository implements AttendanceInterface
{
    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function get(): Collection
    {
        return $this->model->query()->get();
    }

    public function store(array $data): Attendance
    {
        return $this->model->query()->create($data);
    }

    public function show($id): Attendance
    {
        return $this->model->findOrFail($id);
    }

    public function update($id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete($id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function search($request): Collection
    {
        return $this->model->where('name', 'LIKE', '%' . $request->search . '%')->get();
    }

    public function getByStudentAndDate(string $studentId, string $date): mixed
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->first();
    }

    public function getTodayByStudent(string $studentId): mixed
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', today())
            ->first();
    }

    public function getByClassroomAndDate(string $classroomId, string $date): Collection
    {
        return $this->model
            ->whereHas('classroomStudent.classroom', function ($query) use ($classroomId) {
                $query->where('id', $classroomId);
            })
            ->whereDate('date', $date)
            ->get();
    }

    public function getByStudentLesson(string $studentId, string $date, int $lessonOrder): mixed
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->where('lesson_order', $lessonOrder)
            ->first();
    }

    public function getStudentMonthlyAttendance(string $studentId, string $month, string $year): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date')
            ->get();
    }

    public function getByDate(string $date): Collection
    {
        return $this->model
            ->whereDate('date', $date)
            ->orderBy('lesson_order')
            ->get();
    }

    public function getByScheduleAndDate(string $lessonScheduleId, string $date): Collection
    {
        return $this->model
            ->where('lesson_schedule_id', $lessonScheduleId)
            ->whereDate('date', $date)
            ->get();
    }

    public function getByClassroom(string $classroomId): Collection
    {
        return $this->model
            ->whereHas('classroomStudent', function ($query) use ($classroomId) {
                $query->where('classroom_id', $classroomId);
            })
            ->orderBy('date')
            ->orderBy('lesson_order')
            ->get();
    }

    public function getStudentMonthly(string $studentId, string $month): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('lesson_order')
            ->get();
    }
    public function getHistoryByStudentId($studentId, $perPage = 15)
    {
        return Attendance::where('student_id', $studentId)
            ->orderBy('date', 'DESC')
            ->paginate($perPage);
    }                   

    public function getSummary(string $studentId): array
    {
        return $this->model->where('student_id', $studentId)
            ->selectRaw("
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
            ")
            ->first()
            ->toArray();
    }

       public function getDailyRecap(string $date, array $filters = []): array
    {
        $base = $this->model->newQuery()->whereDate('date', $date);

        if (!empty($filters['classroom_id'])) {
            $base->whereHas('classroomStudent', function ($q) use ($filters) {
                $q->where('classroom_id', $filters['classroom_id']);
            });
        }

        if (!empty($filters['major_id'])) {
            $base->whereHas('classroomStudent.classroom', function ($q) use ($filters) {
                $q->where('major_id', $filters['major_id']);
            });
        }

        return [
            'total_attendance_records' => (clone $base)->count(),
            'present' => (clone $base)->where('status', AttendanceStatusEnum::PRESENT->value)->distinct('student_id')->count('student_id'),
            'izin'    => (clone $base)->where('status', AttendanceStatusEnum::LEAVE->value)->distinct('student_id')->count('student_id'), // <-- FIXED
            'sakit'   => (clone $base)->where('status', AttendanceStatusEnum::SICK->value)->distinct('student_id')->count('student_id'),
            'alpha'   => (clone $base)->where('status', AttendanceStatusEnum::ALPHA->value)->distinct('student_id')->count('student_id'),
        ];
    }

    public function getTopAlphaStudents(string $date, array $filters = [], int $limit = 5)
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? $date;

        $attQuery = $this->model->newQuery()
            ->select('student_id', DB::raw('COUNT(*) as alpha_total')) // <-- FIXED
            ->where('status', AttendanceStatusEnum::ALPHA->value)
            ->whereDate('date', '<=', $endDate);

        if ($startDate) {
            $attQuery->whereDate('date', '>=', $startDate);
        }

        if (!empty($filters['classroom_id'])) {
            $attQuery->whereHas('classroomStudent', function ($q) use ($filters) {
                $q->where('classroom_id', $filters['classroom_id']);
            });
        }

        if (!empty($filters['major_id'])) {
            $attQuery->whereHas('classroomStudent.classroom', function ($q) use ($filters) {
                $q->where('major_id', $filters['major_id']);
            });
        }

        $attQuery->groupBy('student_id')
            ->orderByDesc('alpha_total')
            ->limit($limit);

        $rows = DB::table(DB::raw("({$attQuery->toSql()}) as sub"))
            ->mergeBindings($attQuery->getQuery())
            ->get();

        $studentIds = $rows->pluck('student_id')->toArray();
        $countsMap = $rows->mapWithKeys(fn($r) => [$r->student_id => (int)$r->alpha_total])->toArray();

        $students = Student::whereIn('id', $studentIds)
            ->with(['classroomStudents.classroom'])
            ->get()
            ->sortByDesc(fn($s) => $countsMap[$s->id] ?? 0);

        $students->each(function ($s) use ($countsMap) {
            $s->alpha_total = $countsMap[$s->id] ?? 0;
        });

        return $students->values();
    }
    /**
 * Hitung total siswa berdasarkan filter jurusan/kelas
 */
public function totalStudents(array $filters = []): int
{
    $query = Student::query()->whereHas('classroomStudents');

    if (!empty($filters['classroom_id'])) {
        $query->whereHas('classroomStudents', function ($q) use ($filters) {
            $q->where('classroom_id', $filters['classroom_id']);
        });
    }

    if (!empty($filters['major_id'])) {
        $query->whereHas('classroomStudents.classroom', function ($q) use ($filters) {
            $q->where('major_id', $filters['major_id']);
        });
    }

    return $query->count();
}

public function countByStatusOnDate(string $date, array $filters = []): array
{
    $query = $this->model->newQuery()->whereDate('date', $date);

    if (!empty($filters['classroom_id'])) {
        $query->whereHas('classroomStudent', function ($q) use ($filters) {
            $q->where('classroom_id', $filters['classroom_id']);
        });
    }

    if (!empty($filters['major_id'])) {
        $query->whereHas('classroomStudent.classroom', function ($q) use ($filters) {
            $q->where('major_id', $filters['major_id']);
        });
    }

    return [
        'present' => (clone $query)->where('status', AttendanceStatusEnum::PRESENT->value)->count(),
        'izin'    => (clone $query)->where('status', AttendanceStatusEnum::LEAVE->value)->count(),
        'sakit'   => (clone $query)->where('status', AttendanceStatusEnum::SICK->value)->count(),
        'alpha'   => (clone $query)->where('status', AttendanceStatusEnum::ALPHA->value)->count(),
    ];
}

}

       