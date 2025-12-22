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
}