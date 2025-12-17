<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonScheduleInterface;
use App\Models\LessonSchedule;
use Illuminate\Support\Facades\DB;
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
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
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
            ->where('lesson_schedules.day', $day)
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

    public function getByTeacherAndDay(string $teacherId, string $day): Collection
    {
        return $this->model->query()
            ->with([
                'subject', 
                'classroom.major', 
                'classroom.levelClass', 
                'classroom.teacher.user',
                'classroom.schoolYear',
                'classroom.classroomStudents' => function($query) {
                    $query->where('status', 'active');
                },
                'classroom.lessonSchedules.lessonHour',
                'lessonHour', 
                'employee.user'
            ])
            ->where('employee_id', $teacherId)
            ->where('lesson_schedules.day', $day)
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

    public function getByClassroom(string $classroomId)
    {
        return $this->model
            ->with(['lessonHour', 'subject', 'employee.user'])
            ->where('classroom_id', $classroomId)
            ->orderBy('day')
            ->orderBy('lesson_hour_id')
            ->get();
    }

    public function getByClassroomAndDay(string $classroomId, string $day): Collection
    {
        return $this->model->query()
            ->with(['subject', 'classroom.major', 'classroom.levelClass', 'lessonHour', 'employee.user'])
            ->where('classroom_id', $classroomId)
            ->where('lesson_schedules.day', $day)
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

    public function getFirstLessonByClassroomAndDay(string $classroomId, string $day): mixed
    {
        return $this->model->query()
            ->with(['subject', 'classroom.major', 'classroom.levelClass', 'lessonHour', 'employee.user'])
            ->where('classroom_id', $classroomId)
            ->where('lesson_schedules.day', $day)
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->first();
    }

    public function getByTeacherClassroomAndLessonOrder(string $teacherId, string $classroomId, string $day, int $lessonOrder): mixed 
    {
        $lessonHours = \App\Models\LessonHour::where('day', $day)
            ->where('is_lesson', true)
            ->orderBy('start')
            ->get();

        $targetLessonHour = $lessonHours->slice($lessonOrder - 1, 1)->first();

        if (!$targetLessonHour) {
            return null;
        }

        return $this->model->query()
            ->with(['subject', 'classroom.major', 'classroom.levelClass', 'lessonHour', 'employee.user'])
            ->where('employee_id', $teacherId)
            ->where('classroom_id', $classroomId)
            ->where('lesson_schedules.day', $day)
            ->where('lesson_hour_id', $targetLessonHour->id)
            ->first();
    }

    public function checkClassroomConflict(string $classroomId, string $day, string $lessonHourId, ?string $excludeId = null): bool
    {
        $query = $this->model->query()
            ->where('classroom_id', $classroomId)
            ->where('lesson_schedules.day', $day)
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
            ->where('lesson_schedules.day', $day)
            ->where('lesson_hour_id', $lessonHourId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function getByStudentAndDay(string $studentId, string $day): mixed
    {
        return \DB::table('student_lesson_schedules as sls')
            ->join('lesson_schedules as ls', 'ls.id', '=', 'sls.lesson_schedule_id')
            ->join('classrooms as c', 'c.id', '=', 'ls.classroom_id')
            ->join('subjects as s', 's.id', '=', 'ls.subject_id')
            ->join('employees as e', 'e.id', '=', 'ls.employee_id')
            ->where('sls.student_id', $studentId)
            ->where('ls.day', $day)
            ->select('ls.*')
            ->get();
    }

    public function getTodayTeacherSchedule(string $teacherId, string $day)
    {
        return $this->model->query()
            ->with(['classroom.major','classroom.levelClass','lessonHour','subject'])
            ->where('employee_id', $teacherId)
            ->where('lesson_schedules.day', $day)
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

    public function getTodayByTeacher(string $teacherId, string $day): Collection
    {
        return $this->model->query()
            ->with([
                'subject',
                'classroom.major',
                'classroom.levelClass',
                'lessonHour'
            ])
            ->where('employee_id', $teacherId)
            ->where('lesson_schedules.day', $day)
            ->join(
                'lesson_hours',
                'lesson_schedules.lesson_hour_id',
                '=',
                'lesson_hours.id'
            )
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

}