<?php
namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomScheduleResource extends JsonResource
{
    use FormatsTimeTrait;
    
    public function toArray(Request $request): array
    {
        return [
            'classroom' => $this->getClassroomData(),
            'schedules' => $this->getStructuredSchedules(),
        ];
    }

    private function getClassroomData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'homeroom_teacher' => $this->employee?->user?->name ?? '-',
            'total_students' => $this->classroomStudents->where('status', \App\Enums\ClassroomStudentStatusEnum::ACTIVE)->count() ?? 0,
            'school_year' => $this->schoolYear?->name ?? '-',
            'major' => $this->major?->name ?? '-',
            'level_class' => $this->levelClass?->name ?? '-',
        ];
    }

    private function getStructuredSchedules(): array
    {
        $structured = [];

        foreach (DayEnum::cases() as $day) {
            $daySchedules = $this->lessonSchedules
                ->where('day', $day->value)
                ->sortBy('lesson_hour_id')
                ->map(fn($schedule) => $this->formatSchedule($schedule))
                ->values();

            $structured[$day->value] = [
                'day_label' => $day->label(),
                'schedules' => $daySchedules->toArray()
            ];
        }

        return $structured;
    }

    private function formatSchedule($schedule): array
    {
        return [
            'id' => $schedule->id,
            'placement' => $schedule->lessonHour?->name ?? '-',
            'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
            'subject' => $schedule->subject?->name ?? '-',
            'subject_teacher' => $schedule->employee?->user?->name ?? '-',
        ];
    }
}