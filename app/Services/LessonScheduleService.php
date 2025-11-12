<?php

namespace App\Services;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Http\Requests\StoreLessonSchedulesRequest;
use App\Http\Requests\UpdateLessonSchedulesRequest;
use App\Models\LessonSchedule;
use App\Models\Classroom;
use Exception;

class LessonScheduleService
{
    private LessonScheduleInterface $lessonSchedule;

    public function __construct(LessonScheduleInterface $lessonSchedule)
    {
        $this->lessonSchedule = $lessonSchedule;
    }

    public function store(StoreLessonSchedulesRequest $request): LessonSchedule
    {
        $data = $request->validated();
        $this->validateScheduleConflict($data);

        return $this->lessonSchedule->store($data);
    }

    public function update(string $id, UpdateLessonSchedulesRequest $request): LessonSchedule
    {
        $lessonSchedule = LessonSchedule::findOrFail($id);
        $data = $request->validated();
        
        $this->validateScheduleConflict($data, $id);

        $this->lessonSchedule->update($id, $data);

        return $lessonSchedule->fresh([
            'classroom.schoolYear',
            'employee.user',
            'lessonHour',
            'subject',
        ]);
    }

    public function show(string $id): LessonSchedule
    {
        return LessonSchedule::with([
            'classroom.schoolYear',
            'employee.user',
            'lessonHour',
            'subject',
        ])->findOrFail($id);
    }

    public function delete(string $id): bool
    {
        return $this->lessonSchedule->delete($id);
    }

        public function getAllClassroomsWithSchedules()
    {
        return Classroom::with([
            'employee.user',
            'schoolYear', 
            'major',
            'levelClass',
            'classroomStudents.student.user',
            'lessonSchedules.lessonHour',
            'lessonSchedules.subject',
            'lessonSchedules.employee.user'
        ])->get();
    }

    public function getByClassroom(string $classroomId): array
    {
        $classroom = Classroom::with([
            'employee.user',
            'schoolYear', 
            'major',
            'levelClass',
            'classroomStudents.student.user',
            'lessonSchedules.lessonHour',
            'lessonSchedules.subject',
            'lessonSchedules.employee.user'
        ])->findOrFail($classroomId);

        $schedules = $classroom->lessonSchedules
            ->sortBy('lesson_hour_id')
            ->groupBy('day');

        return [
            'classroom' => $classroom,
            'schedules' => $schedules,
        ];
    }

    public function getByClassroomAndDay(string $classroomId, string $day): array
    {
        $classroom = Classroom::with([
            'employee.user',
            'schoolYear', 
            'major',
            'levelClass',
            'classroomStudents.student.user'
        ])->findOrFail($classroomId);

        $schedules = LessonSchedule::with([
            'lessonHour', 
            'subject', 
            'employee.user'
        ])
        ->where('classroom_id', $classroomId)
        ->where('day', $day)
        ->orderBy('lesson_hour_id')
        ->get();

        return [
            'classroom' => $classroom,
            'day' => $day,
            'schedules' => $schedules,
        ];
    }

    private function validateScheduleConflict(array $data, ?string $excludeId = null): void
    {
        $classroomConflict = LessonSchedule::where('classroom_id', $data['classroom_id'])
            ->where('day', $data['day'])
            ->where('lesson_hour_id', $data['lesson_hour_id']);

        if ($excludeId) {
            $classroomConflict->where('id', '!=', $excludeId);
        }

        if ($classroomConflict->exists()) {
            throw new Exception('Kelas sudah memiliki jadwal di hari dan jam yang sama.');
        }

        $teacherConflict = LessonSchedule::where('employee_id', $data['employee_id'])
            ->where('day', $data['day'])
            ->where('lesson_hour_id', $data['lesson_hour_id']);

        if ($excludeId) {
            $teacherConflict->where('id', '!=', $excludeId);
        }

        if ($teacherConflict->exists()) {
            throw new Exception('Guru sudah memiliki jadwal mengajar di hari dan jam yang sama.');
        }
    }
}