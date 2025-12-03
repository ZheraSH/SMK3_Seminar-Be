<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentLessonScheduleInterface;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class StudentLessonScheduleService
{
    public function __construct(
        private StudentLessonScheduleInterface $scheduleRepo
    ) {}

    public function getSchedule(string $studentId, ?string $day = null): array
    {
        $student = $this->scheduleRepo->getStudentById($studentId);
        $classroomId = $student->classroomStudents()
            ->where('status', 'ACTIVE')
            ->first()?->classroom_id;

        $allHours = $this->scheduleRepo->getAllLessonHoursByDay($day);

        $schedules = $this->scheduleRepo
            ->getSchedule($studentId, $day)
            ->filter(fn($s) => $s->classroom_id === $classroomId)
            ->groupBy('lesson_hour_id');


        $formatted = [];
        $order = 1;

        foreach ($allHours as $hour) {
            $startTime = Carbon::parse($hour->start)->format('H:i');
            $endTime   = Carbon::parse($hour->end)->format('H:i');

            if (!$hour->is_lesson) {
                $formatted[] = [
                    'no' => $order++,
                    'jam' => "{$startTime} - {$endTime}",
                    'penempatan' => $this->getBreakLabel($hour->name),
                    'mata_pelajaran' => 'Istirahat',
                    'guru' => null,
                ];
            } else {
                if (isset($schedules[$hour->id])) {
                    foreach ($schedules[$hour->id] as $schedule) {
                        $formatted[] = [
                            'no' => $order++,
                            'jam' => "{$startTime} - {$endTime}",
                            'penempatan' => $hour->name,
                            'mata_pelajaran' => $schedule->subject->name ?? '-',
                            'guru' => $schedule->employee->user->name ?? '-',
                        ];
                    }
                } else {
                    $formatted[] = [
                        'no' => $order++,
                        'jam' => "{$startTime} - {$endTime}",
                        'penempatan' => $hour->name,
                        'mata_pelajaran' => '-',
                        'guru' => null,
                    ];
                }   
            }
        }   

        return $formatted;
    }

    private function getBreakLabel(string $breakName): string
    {
        $breakMap = [
            'Istirahat' => 'Istirahat Pertama',
            'Istirahat 1' => 'Istirahat Pertama',
            'Istirahat 2' => 'Istirahat Ke Dua',
            'break_1' => 'Istirahat Pertama',
            'break_2' => 'Istirahat Ke Dua'
        ];

        return $breakMap[$breakName] ?? $breakName;
    }

    public function getDayName(?string $day): string
    {
        $map = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu'
        ];

        return isset($map[strtolower((string)$day)])
            ? $map[strtolower($day)]
            : 'Semua Hari';
    }
}
