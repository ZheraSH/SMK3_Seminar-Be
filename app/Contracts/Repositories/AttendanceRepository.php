<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Models\Attendance;
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
        return $this->model->get();
    }

    public function paginate($perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function search($request): Collection
    {
        return $this->model->where('name', 'LIKE', '%' . $request->search . '%')->get();
    }

    public function store(array $data): Attendance
    {
        return $this->model->create($data);
    }

    public function update($id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function show($id): Attendance
    {
        return $this->model->findOrFail($id);
    }

    public function delete($id): bool
    {
        return $this->model->findOrFail($id)->delete();
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
            ->where('classroom_id', $classroomId)
            ->whereDate('date', $date)
            ->get();
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
            ->orderBy('period')
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
            ->where('classroom_id', $classroomId)
            ->orderBy('period')
            ->get();
    }

    public function getStudentMonthly(string $studentId, string $month): Collection
    {
        return $this->model
            ->where('student_id', $studentId)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->orderBy('period')
            ->get();
    }
}