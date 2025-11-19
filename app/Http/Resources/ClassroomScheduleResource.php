<?php

namespace App\Http\Resources;

use App\Enums\DayEnum;
use App\Traits\FormatsTimeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ClassroomScheduleResource extends JsonResource
{
    use FormatsTimeTrait;
    
    public function toArray(Request $request): array
    {
        $classroom = $this->resource;
        
        $lessonSchedules = $classroom->lessonSchedules;
        if (is_array($lessonSchedules)) {
            $lessonSchedules = collect($lessonSchedules);
        }
        
        $schedules = $lessonSchedules
            ->sortBy('lesson_hour_id')
            ->groupBy('day');

        return [
            'classroom' => [
                'id' => $classroom->id,
                'name' => $classroom->name,
                'major' => $classroom->major?->code ?? 'Belum ada jurusan',
                'level_class' => $classroom->levelClass?->name ?? 'Belum ada tingkat',
                'school_year' => $classroom->schoolYear?->name ?? 'Belum ada tahun ajaran',
                'homeroom_teacher' => $classroom->employee?->user?->name ?? 'Belum ada wali kelas',
                'total_students' => $classroom->classroomStudents->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value)->count(),
            ],
            'schedules' => $this->getStructuredSchedules($schedules),
        ];
    }

    private function getStructuredSchedules($schedules): array
    {
        $structured = [];

        foreach (DayEnum::cases() as $day) {
            $daySchedules = $schedules->get($day->value, collect())
                ->sortBy('lesson_hour_id')
                ->map(fn($schedule) => $this->formatSchedule($schedule))
                ->values();

            $structured[$day->value] = [
                'day_label' => $day->label(),
                'total_lessons' => $daySchedules->count(),
                'schedules' => $daySchedules->toArray()
            ];
        }

        return $structured;
    }

    private function formatSchedule($schedule): array
    {
        return [
            'id' => $schedule->id,
            'placement' => $schedule->lessonHour?->name ?? 'Jam tidak ditemukan',
            'time' => $this->formatTimeRange($schedule->lessonHour?->start, $schedule->lessonHour?->end),
            'subject' => $schedule->subject?->name ?? 'Mata pelajaran tidak ditemukan',
            'subject_teacher' => $schedule->employee?->user?->name ?? 'Guru tidak ditemukan',
        ];
    }
}