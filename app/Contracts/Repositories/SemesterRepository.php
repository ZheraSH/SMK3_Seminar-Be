<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\SemesterInterface;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
class SemesterRepository extends BaseRepository implements SemesterInterface
{
    public function __construct(Semester $semester)
    {
        $this->model = $semester;
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

     public function getActive(): mixed
    {
        return $this->model->query()->where('active', true)->first();
    }

    public function setActive(mixed $id): mixed
    {
        return DB::transaction(function () use ($id) {
          
            $this->model->query()->update(['active' => false]);

            $semester = $this->show($id);
            $semester->update(['active' => true]);

            return $semester;
        });
    }
}