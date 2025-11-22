<?php

namespace App\Services;

use App\Contracts\Interfaces\StudentLessonScheduleInterface;
use App\Helpers\SemesterHelper;
use Illuminate\Database\Eloquent\Collection;

class StudentLessonScheduleService
{
    public function __construct(
        private StudentLessonScheduleInterface $studentLessonScheduleRepository
    ) {}

    public function getSchedule(string $studentId, ?string $day = null): array
{
    $schedules = $this->studentLessonScheduleRepository->getSchedule($studentId, $day);

    if ($schedules->isEmpty()) {
        return [
            'classroom' => null,
            'schedules' => []
        ];
    }

    
    $classroom = optional($schedules->first()->classroom);

    return [
        'classroom' => [
            'classroom' => $classroom->name,
            'semester' => SemesterHelper::getSemester()['semester'],
            'school_year' => optional($classroom->schoolYear)->name,
        ],
    ];
}
    private function formatSchedule($schedules): array
{
    $schedules = collect($schedules)->sortBy(function($schedule) {
        return $schedule->lessonHour->start;
    });

    $formatted = [];
    $order = 1;

    $breakTimes = [
        [
            'start' => '09:15',
            'end' => '10:00',
            'name' => 'Istirahat'
        ],
        [
            'start' => '12:10',
            'end' => '13:00',
            'name' => 'Istirahat Ke Dua'
        ]
    ];

    $timeSlots = [];
    foreach ($schedules as $schedule) {
        $startTime = $this->formatTimeWithoutSeconds($schedule->lessonHour->start);
        $endTime = $this->formatTimeWithoutSeconds($schedule->lessonHour->end);
        $timeKey = $startTime . '-' . $endTime;

        if (!isset($timeSlots[$timeKey])) {
            $timeSlots[$timeKey] = [
                'time_range' => "{$startTime} - {$endTime}",
                'nama_jam' => $schedule->lessonHour->name,
                'schedules' => []
            ];
        }

        $timeSlots[$timeKey]['schedules'][] = $schedule;
    }

    ksort($timeSlots);

    foreach ($timeSlots as $timeSlot) {
        $timeRange = $timeSlot['time_range'];
        $namaJam = $timeSlot['nama_jam'];

        $isBreak = $this->isBreakTime($breakTimes, $timeRange);

        if ($isBreak) {
            $formatted[] = [
                'no' => $order,
                'jam' => $timeRange,
                'penempatan' => $isBreak['name'],
                'mata_pelajaran' => '-',
                'guru' => '-'
            ];
        } else {
            $schedule = $timeSlot['schedules'][0];

            $formatted[] = [
                'no' => $order,
                'jam' => $timeRange,
                'penempatan' => $namaJam,
                'mata_pelajaran' => $schedule->subject->name,
                'guru' => $schedule->employee->user->name
            ];
        }
        $order++;
    }

    return $formatted;
}

    private function formatTimeWithoutSeconds(string $time): string
    {
        return \Carbon\Carbon::parse($time)->format('H:i');
    }

    private function isBreakTime(array $breakTimes, string $timeRange): ?array
    {
        foreach ($breakTimes as $break) {
            $breakRange = "{$break['start']} - {$break['end']}"; 
            if ($timeRange === $breakRange) {
                return $break;
            }
        }
        
        return null;
    }

    public function getDayName(?string $day): string
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa', 
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat'
        ];

        return $days[strtolower($day)] ?? 'Semua Hari';
    }
}