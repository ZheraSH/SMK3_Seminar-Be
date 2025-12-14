<?php
namespace App\Services\Operator;

use App\Contracts\Repositories\Operator\LessonHourRepository;
use App\Http\Requests\Operator\StoreLessonHourRequest;
use App\Http\Requests\Operator\UpdateLessonHourRequest;
use App\Models\LessonHour;
use Illuminate\Support\Facades\DB;

class LessonHourService
{
    private LessonHourRepository $lessonHourRepository;

    public function __construct(LessonHourRepository $lessonHourRepository)
    {
        $this->lessonHourRepository = $lessonHourRepository;
    }

    public function store(StoreLessonHourRequest $request): LessonHour
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($data['day'] instanceof \BackedEnum) {
                $data['day'] = $data['day']->value;
            }

            $this->validateTimeOverlap($data);

            $orderData = $this->calculateOrderBasedOnTime($data['day'], $data['is_lesson'], $data['start']);

            $name = $this->generateName($data['is_lesson'], $orderData['order']);

            $data['order'] = $orderData['order'];
            $data['name'] = $name;

            if ($orderData['needs_reorder']) {
                $this->reorderAfterInsert(
                    $data['day'], 
                    $data['is_lesson'], 
                    $orderData['order']
                );
            }
            
            return $this->lessonHourRepository->store($data);
        });
    }

    public function update(string $id, UpdateLessonHourRequest $request): LessonHour
    {
        return DB::transaction(function () use ($id, $request) {
            $lessonHour = $this->lessonHourRepository->show($id);
            $data = $request->validated();

            $dayString = $lessonHour->day instanceof \BackedEnum 
                ? $lessonHour->day->value 
                : (string) $lessonHour->day;

            $this->validateTimeOverlap($data, $dayString, $id);

            $this->lessonHourRepository->update($id, [
                'start' => $data['start'],
                'end' => $data['end'],
            ]);
            
            return $this->lessonHourRepository->show($id);
        });
    }

    public function delete(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $lessonHour = $this->lessonHourRepository->show($id);

            if ($this->lessonHourRepository->isUsedInSchedules($id)) {
                throw new \Exception('Tidak dapat menghapus jam pelajaran karena sedang digunakan dalam jadwal');
            }
            
            // Konversi DayEnum ke string
            $dayString = $lessonHour->day instanceof \BackedEnum 
                ? $lessonHour->day->value 
                : (string) $lessonHour->day;
            $isLesson = $lessonHour->is_lesson;

            $deleted = $this->lessonHourRepository->delete($id);

            if ($deleted) {
                $this->reorderAndRenameByDayAndType($dayString, $isLesson);
            }
            
            return $deleted;
        });
    }

    public function getByDay(string $day)
    {
        return $this->lessonHourRepository->getByDay($day);
    }

    private function validateTimeOverlap(array $data, ?string $day = null, ?string $excludeId = null): void
    {
        if ($day === null) {
            $day = $data['day'];

            if ($day instanceof \BackedEnum) {
                $day = $day->value;
            }
        }
        
        if ($this->lessonHourRepository->checkTimeOverlap(
            $day,
            $data['start'],
            $data['end'],
            $excludeId
        )) {
            throw new \Exception(
                'Waktu jam pelajaran bertabrakan dengan jam pelajaran lainnya di hari yang sama'
            );
        }
    }

    private function calculateOrderBasedOnTime(string $day, bool $isLesson, string $startTime): array
    {
        $allHours = $this->lessonHourRepository->getByDay($day);

        $sameTypeHours = $allHours->where('is_lesson', $isLesson)->sortBy('start');

        $position = 1;
        $needsReorder = false;
        
        foreach ($sameTypeHours as $hour) {
            if (strtotime($startTime) < strtotime($hour->start)) {
                $needsReorder = true;
                break;
            }
            $position++;
        }
        
        return [
            'order' => $position,
            'needs_reorder' => $needsReorder
        ];
    }

    private function generateName(bool $isLesson, int $order): string
    {
        return $isLesson ? "Jam ke - {$order}" : "Istirahat - {$order}";
    }

    private function reorderAfterInsert(string $day, bool $isLesson, int $insertPosition): void
    {
        $allHours = $this->lessonHourRepository->getByDay($day)
            ->where('is_lesson', $isLesson)
            ->sortBy('start');
        
        $currentOrder = 1;
        foreach ($allHours as $hour) {
            if ($hour->order >= $insertPosition) {
                $newName = $this->generateName($isLesson, $currentOrder);
                $this->lessonHourRepository->update($hour->id, [
                    'order' => $currentOrder,
                    'name' => $newName,
                ]);
            }
            $currentOrder++;
        }
    }

    private function reorderAndRenameByDayAndType(string $day, bool $isLesson): void
    {
        $hours = $this->lessonHourRepository->getByDay($day)
            ->where('is_lesson', $isLesson)
            ->sortBy('start');
        
        $order = 1;
        foreach ($hours as $hour) {
            $newName = $this->generateName($isLesson, $order);
            $this->lessonHourRepository->update($hour->id, [
                'order' => $order,
                'name' => $newName,
            ]);
            $order++;
        }
    }
}