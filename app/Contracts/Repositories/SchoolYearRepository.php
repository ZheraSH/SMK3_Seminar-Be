<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Models\SchoolYear;

class SchoolYearRepository extends BaseRepository implements SchoolYearInterface
{
    public function __construct(SchoolYear $schoolYear)
    {
        $this->model = $schoolYear;
    }

    public function get(): mixed
    {
        return $this->model->all();
    }

    public function show(mixed $id): mixed
    {
        return $this->model->findOrFail($id);
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->model->latest()->paginate(12);
    }

    public function active(): mixed
    {
        return $this->model->where('active', true)->first();
    }

    public function latest(): mixed
    {
        return $this->model->orderByDesc('name')->first();
    }

    public function setActive($id): mixed
    {
        return $this->show($id)->update(['active' => true]);
    }

    public function unsetAll(): mixed
    {
        return $this->model->query()->update(['active' => false]);
    }
}
