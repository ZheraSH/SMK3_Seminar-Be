<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceRuleInterface;
use App\Models\AttendanceRule;

class AttendanceRuleRepository extends BaseRepository implements AttendanceRuleInterface
{
    public function __construct(AttendanceRule $attendanceRule)
    {
        $this->model = $attendanceRule;
    }

    public function get(): mixed
    {
        return $this->model->query()->get();
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
        return $this->model->query()->where('day', $day)->first();
    }
}