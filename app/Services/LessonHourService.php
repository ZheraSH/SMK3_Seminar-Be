<?php
namespace App\Services;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Http\Requests\StoreLessonHourRequest;
use App\Models\LessonHour;
use Illuminate\Http\Request;

class LessonHourService
{
    private LessonHourInterface $lessonHourInterface;

    public function __construct(LessonHourInterface $lessonHourInterface)
    {
        $this->lessonHourInterface = $lessonHourInterface;
    }

    
    public function store(StoreLessonHourRequest $request): LessonHour
    {
        $data = $request->validated();
        
        $this->validateUniqueName($data['name'], $data['day']);
        $this->validateTimeOverlap($data);
        
        return $this->lessonHourInterface->store($data);
    }
    
    public function show(string $id): LessonHour
    {
        return $this->lessonHourInterface->show($id);
    }

    public function delete(string $id): bool
    {
        if ($this->lessonHourInterface->isUsedInSchedules($id)) {
            throw new \Exception('Tidak dapat menghapus jam pelajaran karena sedang digunakan dalam jadwal');
        }
        
        return $this->lessonHourInterface->delete($id);
    }
    
    public function getAll(Request $request)
    {
        return $this->lessonHourInterface->get();
    }

    public function getAllGroupedByDay()
    {
        $lessonHours = $this->lessonHourInterface->get();
        
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

    public function getByDay(string $day)
    {
        return $this->lessonHourInterface->getByDay($day);
    }

    private function validateUniqueName(string $name, string $day, ?string $excludeId = null): void
    {
        if ($this->lessonHourInterface->checkNameExists($name, $day, $excludeId)) {
            throw new \Exception('Nama jam pelajaran "' . $name . '" sudah digunakan untuk hari ' . $day);
        }
    }

    private function validateTimeOverlap(array $data, ?string $excludeId = null): void
    {
        if ($this->lessonHourInterface->checkTimeOverlap($data['day'], $data['start'], $data['end'], $excludeId)) {
            throw new \Exception('Waktu jam pelajaran bertabrakan dengan jam pelajaran lainnya di hari yang sama');
        }
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