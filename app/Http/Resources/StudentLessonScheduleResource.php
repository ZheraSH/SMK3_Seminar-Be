<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentLessonScheduleResource extends JsonResource
{
    public function toArray($request)
    {
        if ($this->is_break) {
            return [
                'no' => $this->number,
                'jam' => "{$this->lessonHour->start} - {$this->lessonHour->end}",
                'penempatan' => $this->break_type ?? 'Istirahat',
                'mata_pelajaran' => '-',
                'guru' => '-'
            ];
        }

        $start = $this->lessonHour->session_start;
        $end = $this->lessonHour->session_end;

        if (!$start && !$end) {
            $penempatan = "Jam Ke {$this->number}";
        } elseif ($start == $end) {
            $penempatan = "Jam Ke {$start}";
        } else {
            $penempatan = "Jam Ke {$start} - {$end}";
        }

        return [
            'no' => $this->number,
            'jam' => "{$this->lessonHour->start} - {$this->lessonHour->end}",
            'penempatan' => $penempatan,
            'mata_pelajaran' => $this->subject->name ?? '-',
            'guru' => $this->employee->user->name ?? '-'
        ];
    }
}
