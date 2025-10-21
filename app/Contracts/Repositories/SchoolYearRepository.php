<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Models\SchoolYear;

class SchoolYearRepository implements SchoolYearInterface
{
    protected $model;

    public function __construct(SchoolYear $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->orderByDesc('created_at')->get();
    }

    public function find($id)
    {
        return $this->model->withTrashed()->find($id);
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function update($model, array $data)
    {
        $model->update($data);
        return $model;
    }

    public function delete($model)
    {
        return $model->delete();
    }

    public function restore($id)
    {
        $data = $this->model->onlyTrashed()->find($id);
        if ($data) {
            $data->restore();
        }
        return $data;
    }
}
