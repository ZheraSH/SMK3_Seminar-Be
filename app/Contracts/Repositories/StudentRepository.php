<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\StudentInterface;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentRepository extends BaseRepository implements StudentInterface
{
    public function __construct(Student $student)
    {
        $this->model = $student;
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

    public function get(): mixed
    {
        return $this->model->query()->get();
    }

    public function delete(mixed $id): mixed
    {
        return $this->model->query()->findOrFail($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->model->query()->latest()->paginate(8);
    }

    public function search(Request $request): mixed
    {
        return $this->model->query()
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                })->orWhereHas('nisn', function($q) use ($request){
                    $q->where('name', 'LIKE', '%' . $request->search . '%');
                })->orWhereHas('classromStudents',function($q) use ($request){
                    $q->where('classrooms.name', 'LIKE', '%' . $request->search . '%');
                });
            })
            ->when($request->filter, function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->latest()
            ->paginate(8);
    }

    public function count(): mixed
    {
        return $this->model->query()->count();
    }
}
