<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\MajorInterface;
use App\Models\Major;

class MajorRepository extends BaseRepository implements MajorInterface
{
    public function __construct(Major $Major)
    {
        $this->model = $Major;
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