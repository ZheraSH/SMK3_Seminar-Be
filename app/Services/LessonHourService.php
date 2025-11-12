<?php
namespace App\Services;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Http\Requests\StoreLessonHourRequest;
use App\Models\LessonHour;

class LessonHourService
{
    private LessonHourInterface $lessonHour;

    public function __construct(LessonHourInterface $lessonHour)
    {
        $this->lessonHour = $lessonHour;
    }

    public function store(StoreLessonHourRequest $request): LessonHour
    {
        $data = $request->validated();
        
        $this->validateUniqueName($data['name'], $data['day']);
        
        $this->validateTimeOverlap($data);
        
        return $this->lessonHour->store($data);
    }

    public function delete(LessonHour $lessonHour): bool
    {
        if ($this->isUsedInSchedules($lessonHour->id)) {
            throw new \Exception('Tidak dapat menghapus jam pelajaran karena sedang digunakan dalam jadwal');
        }
        
        return $this->lessonHour->delete($lessonHour->id);
    }

    public function getAllGroupedByDay()
    {
        $lessonHours = $this->lessonHour->get();
        
        return $lessonHours->groupBy('day')->map(function ($hours) {
            return $hours->map(function ($hour) {
                return [
                    'id' => $hour->id,
                    'name' => $hour->name,
                    'start_time' => $this->formatTimeForResponse($hour->start),
                    'end_time' => $this->formatTimeForResponse($hour->end),
                    'time_range' => $this->getTimeRangeForResponse($hour->start, $hour->end),
                ];
            })->values();
        });
    }

    private function validateUniqueName(string $name, string $day): void
    {
        $existing = LessonHour::where('name', $name)
            ->where('day', $day)
            ->exists();

        if ($existing) {
            throw new \Exception('Nama jam pelajaran "' . $name . '" sudah digunakan untuk hari ' . $day);
        }
    }

    private function validateTimeOverlap(array $data, ?string $excludeId = null): void
    {
        $overlapQuery = LessonHour::where('day', $data['day'])
            ->where(function ($query) use ($data) {
                $query->where('start', '<', $data['end'])
                      ->where('end', '>', $data['start']);
            });

        if ($excludeId) {
            $overlapQuery->where('id', '!=', $excludeId);
        }

        if ($overlapQuery->exists()) {
            throw new \Exception('Waktu jam pelajaran bertabrakan dengan jam pelajaran lainnya di hari yang sama');
        }
    }

    private function isUsedInSchedules(string $lessonHourId): bool
    {
        return \App\Models\LessonSchedule::where('lesson_hour_id', $lessonHourId)->exists();
    }

    private function formatTimeForResponse($time): string
    {
        if (!$time) return '';
        if ($time instanceof \DateTime || $time instanceof \Carbon\Carbon) {
            return $time->format('H.i');
        }
        if (is_string($time) && preg_match('/^(\d{1,2}):(\d{2})/', $time, $matches)) {
            return $matches[1] . '.' . $matches[2];
        }
        return (string) $time;
    }

    private function getTimeRangeForResponse($start, $end): string
    {
        $startFormatted = $this->formatTimeForResponse($start);
        $endFormatted = $this->formatTimeForResponse($end);
        return $startFormatted && $endFormatted ? $startFormatted . ' - ' . $endFormatted : '';
    }
}