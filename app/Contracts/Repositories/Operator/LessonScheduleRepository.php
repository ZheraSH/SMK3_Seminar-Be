<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\LessonScheduleInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Models\LessonSchedule;
use App\Models\LessonHour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LessonScheduleRepository extends BaseRepository implements LessonScheduleInterface
{
    public function __construct(LessonSchedule $lessonSchedule)
    {
        $this->model = $lessonSchedule;
    }

    protected function baseQuery(): Builder
    {
        return $this->model->query()
            ->with([
                'classroom.major',
                'classroom.levelClass',
                'classroom.schoolYear',
                'classroom.homeroomTeacher',
                'teacher.user',
                'lessonHour',
                'subject',
            ]);
    }

    public function get(): Collection
    {
        return $this->baseQuery()
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.start')
            ->select('lesson_schedules.*')
            ->get();
    }

    public function store(array $data): LessonSchedule
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): LessonSchedule
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    protected function conflictQuery(string $column, string $value, string $day, string $lessonHourId, ?string $excludeId): bool
    {
        $query = $this->model->query()
            ->where($column, $value)
            ->where('day', $day)
            ->where('lesson_hour_id', $lessonHourId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function checkClassroomConflict(string $classroomId, string $day, string $lessonHourId, ?string $excludeId = null): bool
    {
        return $this->conflictQuery(
            'classroom_id',
            $classroomId,
            $day,
            $lessonHourId,
            $excludeId
        );
    }

    public function checkTeacherConflict(string $teacherId, string $day, string $lessonHourId, ?string $excludeId = null): bool
    {
        return $this->conflictQuery(
            'teacher_id',
            $teacherId,
            $day,
            $lessonHourId,
            $excludeId
        );
    }

    public function getLessonScheduleClassroomAndDay(string $classroomId, string $day): Collection
    {
        return $this->baseQuery()
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->whereHas('lessonHour', fn($q) => $q->where('is_lesson', true))
            ->orderBy(
                LessonHour::select('start')
                    ->whereColumn('lesson_hours.id', 'lesson_schedules.lesson_hour_id')
            )
            ->get();
    }

    //Teacher
    public function getByTeacherClassroomAndLessonOrder(string $teacherId, string $classroomId, string $day, int $lessonOrder): ?LessonSchedule
    {
        return $this->baseQuery()
            ->where('teacher_id', $teacherId)
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->whereHas('lessonHour', function ($q) use ($lessonOrder) {
                $q->where('order', $lessonOrder);
            })
            ->first();
    }

    public function getByTeacherAndDayWithLessonHour(string $teacherId, string $day): Collection
    {
        return $this->baseQuery()
            ->join('lesson_hours', 'lesson_schedules.lesson_hour_id', '=', 'lesson_hours.id')
            ->where('lesson_schedules.teacher_id', $teacherId)
            ->where('lesson_schedules.day', $day)
            ->where('lesson_hours.is_lesson', true)
            ->orderBy('lesson_hours.order', 'ASC')
            ->select('lesson_schedules.*')
            ->get();
    }
    //Teacher Close
}
