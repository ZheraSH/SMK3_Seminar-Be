<?php
namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Contracts\Interfaces\ClassroomInterface;
use App\Http\Requests\StoreLessonSchedulesRequest;
use App\Http\Requests\UpdateLessonSchedulesRequest;
use App\Models\LessonSchedule;
use Exception;

class LessonScheduleService
{
    private LessonScheduleInterface $lessonSchedule;
    private ClassroomInterface $classroom;

    public function __construct(LessonScheduleInterface $lessonSchedule, ClassroomInterface $classroom)
    {
        $this->lessonSchedule = $lessonSchedule;
        $this->classroom = $classroom;
    }

    public function store(StoreLessonSchedulesRequest $request): LessonSchedule
    {
        $data = $request->validated();
        $this->validateScheduleConflict($data);

        return $this->lessonSchedule->store($data);
    }

    public function update(string $id, UpdateLessonSchedulesRequest $request): LessonSchedule
    {
        $data = $request->validated();
        $this->validateScheduleConflict($data, $id);

        $this->lessonSchedule->update($id, $data);

        return $this->lessonSchedule->show($id);
    }

    public function show(string $id): LessonSchedule
    {
        return $this->lessonSchedule->show($id);
    }

    public function delete(string $id): bool
    {
        return $this->lessonSchedule->delete($id);
    }

    public function getAllClassroomsWithSchedules()
    {
        $classrooms = $this->classroom->getWithSchedules();

        return $classrooms->filter(function($classroom) {
            return $classroom && $classroom->id;
        });
    }

    public function getByClassroom(string $classroomId)
    {
        return $this->classroom->getWithSchedulesById($classroomId);
    }

    public function getByClassroomAndDay(string $classroomId, string $day): array
    {
        $classroom = $this->classroom->getWithSchedulesById($classroomId);
        $schedules = $this->lessonSchedule->getByClassroomAndDay($classroomId, $day);

        return [
            'classroom' => $classroom,
            'day' => $day,
            'schedules' => $schedules,
        ];
    }

    private function validateScheduleConflict(array $data, ?string $excludeId = null): void
    {
        if ($this->lessonSchedule->checkClassroomConflict($data['classroom_id'], $data['day'], $data['lesson_hour_id'], $excludeId)) {
            throw new Exception('Kelas sudah memiliki jadwal di hari dan jam yang sama.');
        }

        if ($this->lessonSchedule->checkTeacherConflict($data['employee_id'], $data['day'], $data['lesson_hour_id'], $excludeId)) {
            throw new Exception('Guru sudah memiliki jadwal mengajar di hari dan jam yang sama.');
        }
    }
}