<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\SchoolYearInterface;
use App\Contracts\Repositories\BaseRepository;
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

    public function paginate($request = null): mixed
    {
        $query = $this->model->latest();

        if ($request?->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        return $query->paginate($request->limit ?? 12);
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

    public function findByNameWithTrashed(string $name): ?SchoolYear
    {
        return $this->model
            ->withTrashed()
            ->where('name', $name)
            ->first();
    }
}
