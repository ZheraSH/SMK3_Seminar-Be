<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\LevelClassInterface;
use App\Models\LevelClass;

class LevelClassRepository extends BaseRepository implements LevelClassInterface
{
    public function __construct(LevelClass $LevelClass)
    {
        $this->model = $LevelClass;
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
        return $this->model->query()->findOrFail($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id)->delete();
    }
}