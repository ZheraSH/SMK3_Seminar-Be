<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ReligionInterface;
use App\Models\Religion;

class ReligionRepository extends BaseRepository implements ReligionInterface
{
    public function __construct(Religion $religion)
    {
        $this->model = $religion;
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