<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TeacherClassroomResource extends JsonResource
{
    public function toArray($request)
    {
        $classroom = $this->classroom;
        $firstSchedule = $this->first_schedule;
        $studentCount = $classroom->classroomStudents ? 
            $classroom->classroomStudents->where('status', 'active')->count() : 0;
        $lessonHour = $firstSchedule->lessonHour ?? null;
        $lessonOrder = $this->calculateLessonOrder($firstSchedule);

        return [
            'id' => $classroom->id,
            'name' => $classroom->name,
            'major' => $classroom->major->code ?? null,
            'level' => $classroom->levelClass->name ?? null,
            'school_year' => $classroom->schoolYear->name ?? '2024/2025',
            'homeroom_teacher' => $classroom->teacher ? [
                'id' => $classroom->teacher->id,
                'name' => $classroom->teacher->user->name ?? 'Tidak diketahui',
                'type' => 'homeroom_teacher',
                'type_label' => 'Wali Kelas',
            ] : null,
            'students' => [
                'total_count' => $studentCount,
            ],
            'lesson_hour' => $lessonHour ? [
                'id' => $lessonHour->id,
                'start_time' => $lessonHour->start ?? $lessonHour->start_time,
                'end_time' => $lessonHour->end ?? $lessonHour->end_time,
                'name' => $lessonHour->name ?? "Jam Ke {$lessonOrder}",
                'lesson_order' => $lessonOrder,
            ] : null,
        ];
    }

    private function calculateLessonOrder($schedule): int
    {
        if (!$schedule->lessonHour) {
            return 1;
        }

        try {
            $lessonHours = \App\Models\LessonHour::where('day', $schedule->day)
                ->where('is_lesson', true)
                ->orderBy('start')
                ->get();

            $position = $lessonHours->search(function ($lessonHour) use ($schedule) {
                return $lessonHour->id === $schedule->lesson_hour_id;
            });

            return $position !== false ? $position + 1 : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }
}