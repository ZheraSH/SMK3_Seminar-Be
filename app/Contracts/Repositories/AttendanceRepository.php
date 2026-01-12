<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceRepository extends BaseRepository implements AttendanceInterface
{
    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    public function get(): Collection
    {
        return $this->model->query()->get();
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

    public function updateOrCreate(array $attributes, array $values): Attendance
    {
        return $this->model->query()->updateOrCreate($attributes, $values);
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

    //Student
    public function getStudentHistory(string $studentId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('attendance_type', 'rfid')
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
            ->whereHas('classroomStudent', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            })
            ->whereDate('date', $date)
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

    public function updateByPermission(string $studentId, array $dateRange, string $permissionId, string $status): int
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereBetween('date', $dateRange)
            ->where('is_locked', false)
            ->update([
                'status' => $status,
                'is_locked' => true,
                'overridden_by_permission_id' => $permissionId,
                'is_final' => true
            ]);
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

    //Teacher Close
}
