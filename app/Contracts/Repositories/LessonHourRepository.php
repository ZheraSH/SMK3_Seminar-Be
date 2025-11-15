<?php
namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LessonHourInterface;
use App\Models\LessonHour;

class LessonHourRepository extends BaseRepository implements LessonHourInterface
{
    public function __construct(LessonHour $lessonHour)
    {
        $this->model = $lessonHour;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->orderBy('day')
            ->orderBy('start')
            ->get();
    }
    
    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function getByDay(string $day): mixed
    {
        return $this->model->query()
            ->where('day', $day)
            ->orderBy('start')
            ->get();
    }
    
    public function checkNameExists(string $name, string $day, ?string $excludeId = null): bool
    {
        $query = $this->model->query()
            ->where('name', $name)
            ->where('day', $day);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function checkTimeOverlap(string $day, string $start, string $end, ?string $excludeId = null): bool
    {
        $query = $this->model->query()
            ->where('day', $day)
            ->where(function ($q) use ($start, $end) {
                $q->where('start', '<', $end)
                  ->where('end', '>', $start);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function isUsedInSchedules(string $lessonHourId): bool
    {
        return \App\Models\LessonSchedule::where('lesson_hour_id', $lessonHourId)->exists();
    }
}