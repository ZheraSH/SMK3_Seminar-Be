<?php
namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomDayScheduleResource extends JsonResource
{
    use FormatsTimeTrait;

    public function toArray(Request $request): array
    {
        // $this->resource adalah array dari service
        $classroom = $this->resource['classroom'];
        $day = $this->resource['day'];
        $schedules = $this->resource['schedules'];

        // Hitung total students manual
        $totalStudents = 0;
        if (isset($classroom->classroomStudents)) {
            $totalStudents = $classroom->classroomStudents
                ->where('status', \App\Enums\ClassroomStudentStatusEnum::ACTIVE)
                ->count();
        }

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'homeroom_teacher' => $classroom->employee?->user?->name ?? '-',
                'total_students' => $totalStudents,
                'school_year' => $classroom->schoolYear?->name ?? '-',
                'major' => $classroom->major?->name ?? '-',
                'level_class' => $classroom->levelClass?->name ?? '-',
            ],
            'day' => [
                'value' => $day,
                'label' => DayEnum::tryFrom($day)?->label() ?? $day,
            ],
            'schedules' => $this->getDaySchedules($schedules),
            'summary' => [
                'total_lessons' => $schedules->count(),
                'total_subjects' => $schedules->unique('subject_id')->count(),
                'total_teachers' => $schedules->unique('employee_id')->count(),
            ],
        ];
    }

    private function getDaySchedules($schedules): array
    {
        return $schedules
            ->map(fn($schedule) => [
                'id' => $schedule->id,
                'placement' => $schedule->lessonHour?->name ?? '-',
                'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
                'subject' => $schedule->subject?->name ?? '-',
                'subject_teacher' => $schedule->employee?->user?->name ?? '-',
                'subject_id' => $schedule->subject_id,
                'employee_id' => $schedule->employee_id,
                'lesson_hour_id' => $schedule->lesson_hour_id,
            ])
            ->values()
            ->toArray();
    }
}