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
}