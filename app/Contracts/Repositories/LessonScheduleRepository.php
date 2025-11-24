<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Models\LessonSchedule;
use Illuminate\Database\Eloquent\Collection;

class LessonScheduleRepository extends BaseRepository implements LessonScheduleInterface
{
    public function __construct(LessonSchedule $lessonSchedule)
    {
        $this->model = $lessonSchedule;
    }

    public function get(): Collection
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function store(array $data): LessonSchedule
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): LessonSchedule
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        $record = $this->show($id);
        return $record->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    public function getByDay(string $day): Collection
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->where('day', $day)
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function getByTeacherAndDay(string $employeeId, string $day): Collection
    {
        return $this->model->query()
            ->with(['subject', 'classroom', 'lessonHour', 'employee.user'])
            ->where('employee_id', $employeeId)
            ->where('day', $day)
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function getByClassroomAndDay(string $classroomId, string $day): Collection
    {
        return $this->model->query()
            ->with(['subject', 'classroom', 'lessonHour', 'employee.user'])
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function getFirstLessonByClassroomAndDay(string $classroomId, string $day): mixed
    {
        return $this->model->query()
            ->with(['subject', 'classroom', 'lessonHour', 'employee.user'])
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->orderBy('lesson_hour_id')
            ->first();
    }

    public function getByTeacherClassroomAndLessonOrder(
        string $employeeId,
        string $classroomId,
        string $day,
        int $lessonHourId
    ): mixed {
        return $this->model->query()
            ->with(['subject', 'classroom', 'lessonHour', 'employee.user'])
            ->where('employee_id', $employeeId)
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->where('lesson_hour_id', $lessonHourId)
            ->first();
    }

    public function checkClassroomConflict(string $classroomId, string $day, string $lessonHourId, ?string $excludeId = null): bool
    {
        $query = $this->model->query()
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->where('lesson_hour_id', $lessonHourId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function checkTeacherConflict(string $employeeId, string $day, string $lessonHourId, ?string $excludeId = null): bool
    {
        $query = $this->model->query()
            ->where('employee_id', $employeeId)
            ->where('day', $day)
            ->where('lesson_hour_id', $lessonHourId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}