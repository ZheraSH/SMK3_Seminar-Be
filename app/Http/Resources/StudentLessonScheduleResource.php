<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentLessonScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        if ($this->is_break) {
            $penempatan = $this->break_type ?? 'Istirahat';
        } else {
            $startSession = $this->lessonHour->session_start ?? $this->lessonHour->hour ?? null;
            $endSession = $this->lessonHour->session_end ?? $this->lessonHour->hour ?? null;

            if ($startSession && $endSession) {
                $penempatan = "Jam ke $startSession - $endSession";
            } elseif ($startSession) {
                $penempatan = "Jam ke $startSession";
            } else {
                $penempatan = "-";
            }
        }

        return [
            'no' => $this->number ?? null,
            'jam' => ($this->lessonHour->start ?? '') . ' - ' . ($this->lessonHour->end ?? ''),
            'penempatan' => $this->lessonHour->name ?? '-', 
            'mata_pelajaran' => $this->is_break ? '-' : ($this->subject->name ?? '-'),
            'guru' => $this->is_break ? '-' : ($this->employee->user->name ?? '-'),
        ];
    }
}
