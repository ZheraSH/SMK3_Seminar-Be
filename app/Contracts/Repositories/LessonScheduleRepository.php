<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Models\LessonSchedule;

class LessonScheduleRepository extends BaseRepository implements LessonScheduleInterface
{
    public function __construct(LessonSchedule $lessonSchedule)
    {
        $this->model = $lessonSchedule;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }
    
    public function getByDay(string $day): mixed
    {
        return $this->model->query()
            ->with(['classroom.schoolYear', 'classroom.major', 'classroom.levelClass', 'employee.user', 'lessonHour', 'subject'])
            ->where('day', $day)
            ->orderBy('lesson_hour_id')
            ->get();
    }
}