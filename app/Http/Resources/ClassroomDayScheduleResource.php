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
        return [
            'classroom' => $this->getClassroomData(),
            'day' => [
                'value' => $this->day,
                'label' => DayEnum::tryFrom($this->day)?->label() ?? $this->day,
            ],
            'schedules' => $this->getDaySchedules(),
        ];
    }

    private function getClassroomData(): array
    {
        return [
            'id' => $this->classroom->id,
            'name' => $this->classroom->name,
            'homeroom_teacher' => $this->classroom->employee?->user?->name ?? '-',
            'total_students' => $this->classroom->classroomStudents->where('status', \App\Enums\ClassroomStudentStatusEnum::ACTIVE)->count() ?? 0,
            'school_year' => $this->classroom->schoolYear?->name ?? '-',
            'major' => $this->classroom->major?->name ?? '-',
            'level_class' => $this->classroom->levelClass?->name ?? '-',
        ];
    }

    private function getDaySchedules(): array
    {
        return $this->schedules
            ->map(fn($schedule) => [
                'id' => $schedule->id,
                'placement' => $schedule->lessonHour?->name ?? '-',
                'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
                'subject' => $schedule->subject?->name ?? '-',
                'subject_teacher' => $schedule->employee?->user?->name ?? '-',
            ])
            ->values()
            ->toArray();
    }
}