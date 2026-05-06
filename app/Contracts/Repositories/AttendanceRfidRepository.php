<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceRfidInterface;
use App\Enums\AttendanceStatusEnum;
use App\Enums\StudentStatusEnum;
use App\Models\AttendanceRfid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;


class AttendanceRfidRepository extends BaseRepository implements AttendanceRfidInterface
{
    public function __construct(AttendanceRfid $attendanceRfid)
    {
        $this->model = $attendanceRfid;
    }

    public function get(): Collection
    {
        return $this->model->query()->get();
    }

    public function store(array $data): AttendanceRfid
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): AttendanceRfid
    {
        return $this->model->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function getByStudentAndDate(string $studentId, string $date): ?AttendanceRfid
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->first();
    }

    public function getByClassroomAndDate(string $classroomId, string $date): Collection
    {
        return $this->model
            ->whereDate('date', $date)
            
            ->whereHas('student.classroomStudents', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId)
                    ->where('status', StudentStatusEnum::ACTIVE->value);
            })
            ->with(['student.user'])
            ->orderBy('checkin_time', 'desc')
            ->get();
    }

    public function getStudentHistory(string $studentId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('student_id', $studentId)
            
            ->orderByDesc('date')
            ->paginate($perPage);
    }

    public function getStudentSummary(string $studentId): array
    {
        return $this->model
            ->where('student_id', $studentId)
            
            ->selectRaw("
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->first()
            ->toArray();
    }

    public function getStudentMonthlyStatistic(string $studentId, int $year): array
    {
        $data = $this->model
            ->where('student_id', $studentId)
            ->whereYear('date', $year)
            
            ->selectRaw("
                MONTH(date) as month,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->groupByRaw('MONTH(date)')
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($data) {
            return [
                'month' => $month,
                'hadir' => $data[$month]->hadir ?? 0,
                'telat' => $data[$month]->telat ?? 0,
            ];
        })->values()->toArray();
    }

    public function countTotalStatusToday(string $date): array
    {
        return $this->model
            ->whereDate('date', $date)
            
            ->selectRaw("
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as hadir,
                COUNT(DISTINCT CASE WHEN status = ? THEN student_id END) as telat
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->first()
            ->toArray();
    }

    public function countTotalStatusGlobal(): array
    {
        $data = $this->model->query()
            
            ->selectRaw("
                COUNT(CASE WHEN status = ? THEN 1 END) as hadir,
                COUNT(CASE WHEN status = ? THEN 1 END) as terlambat,
                COUNT(*) as total
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->first();

        return $data ? $data->toArray() : [
            'hadir' => 0,
            'terlambat' => 0,
            'total' => 0
        ];
    }

    public function countTotalStatusMonthly(int $year): array
    {
        $data = $this->model->query()
            ->whereYear('date', $year)
            
            ->selectRaw("
                MONTH(date) as month,
                COUNT(CASE WHEN status = ? THEN 1 END) as hadir,
                COUNT(CASE WHEN status = ? THEN 1 END) as terlambat
            ", [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->groupByRaw('MONTH(date)')
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function ($month) use ($data) {
            return [
                'month' => $month,
                'hadir' => $data[$month]->hadir ?? 0,
                'terlambat' => $data[$month]->terlambat ?? 0,
            ];
        })->values()->toArray();
    }

    public function getMonthlyAttendanceSummaryPerStudent(int $month, int $year): Collection
    {
        return $this->model->query()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            
            ->select('student_id')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as hadir", [AttendanceStatusEnum::PRESENT->value])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as telat", [AttendanceStatusEnum::LATE->value])
            ->groupBy('student_id')
            ->get();
    }

    public function getRecentActivities(int $limit = 10)
    {
        return $this->model->query()
            ->join('students', 'attendance_rfids.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classroom_students', function ($join) {
                $join->on('students.id', '=', 'classroom_students.student_id')
                    ->where('classroom_students.status', '=', 'active');
            })
            ->leftJoin('classrooms', 'classroom_students.classroom_id', '=', 'classrooms.id')
            ->whereDate('attendance_rfids.date', now())
            ->orderByDesc('attendance_rfids.checkin_time')
            ->limit($limit)
            ->get([
                'attendance_rfids.id',
                'users.name',
                'classrooms.name as classroom',
                'attendance_rfids.status',
                'attendance_rfids.checkin_time',
                'attendance_rfids.checkout_time',
                'attendance_rfids.date',
            ]);
    }

    public function getPaginatedHistory(int $perPage = 10, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->join('students', 'attendance_rfids.student_id', '=', 'students.id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->leftJoin('classroom_students', function ($join) {
                $join->on('students.id', '=', 'classroom_students.student_id')
                    ->where('classroom_students.status', '=', 'active');
            })
            ->leftJoin('classrooms', 'classroom_students.classroom_id', '=', 'classrooms.id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                  ->orWhere('classrooms.name', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('attendance_rfids.status', $status);
        }

        return $query->orderByDesc('attendance_rfids.date')
            ->orderByDesc('attendance_rfids.checkin_time')
            ->select([
                'attendance_rfids.id',
                'users.name',
                'classrooms.name as classroom',
                'attendance_rfids.status',
                'attendance_rfids.date',
                'attendance_rfids.checkin_time',
                'attendance_rfids.checkout_time',
            ])
            ->paginate($perPage);
    }

    public function getTodaySummary()
    {
        return $this->model->query()
            ->whereDate('date', now())
            ->selectRaw('
                 COUNT(CASE WHEN status = ? THEN 1 END) as present,
                 COUNT(CASE WHEN status = ? THEN 1 END) as late
             ', [
                AttendanceStatusEnum::PRESENT->value,
                AttendanceStatusEnum::LATE->value,
            ])
            ->first();
    }


    public function getMonthlyChart(int $year)
    {
        return $this->model->query()
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
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getStudentsWithHighAlpha(int $threshold, int $limit = 10): Collection
    {
        return $this->model
            ->where('status', AttendanceStatusEnum::ALPHA->value)
            
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
}
