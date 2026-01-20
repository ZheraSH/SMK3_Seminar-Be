<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\MajorInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Models\Major;

class MajorRepository extends BaseRepository implements MajorInterface
{
    public function __construct(Major $major)
    {
        $this->model = $major;
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

    public function count(): int
    {
        return $this->model->query()->count();
    }
}
