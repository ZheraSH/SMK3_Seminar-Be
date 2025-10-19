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
            ->when($request->name, function ($query) use ($request) {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->name . '%');
                });
            })
            ->when($request->gender, function ($query) use ($request) {
                $query->where('gender', $request->gender);
            })
            ->when($request->nisn, function ($query) use ($request) {
                $query->where('nisn', 'LIKE', '%' . $request->nisn . '%');
            })
            ->when($request->birth_place, function ($query) use ($request) {
                $query->where('birth_place', 'LIKE', '%' . $request->birth_place . '%');
            })
            ->when($request->address, function ($query) use ($request) {
                $query->where('address', 'LIKE', '%' . $request->address . '%');
            })
            ->when($request->religion_id, function ($query) use ($request) {
                $query->where('religion_id', $request->religion_id);
            })
            ->when($request->class, function ($query) use ($request) {
                $query->whereHas('classroomStudents.classroom', function ($query) use ($request) {
                    $query->where('name', 'LIKE', '%' . $request->class . '%');
                });
            })
            ->whereDoesntHave('classroomStudents.classroom.levelClass', function($query) {
                $query->where('name');
            })
            ->latest()
            ->paginate(8);
    }
    
    // public function doesntHaveClassroom(Request $request): mixed
    // {
    //     return $this->model->query()
    //         ->with('user')
    //         ->whereDoesntHave('classroomStudents')
    //         ->when($request->name, function ($query) use ($request) {
    //             $query->whereHas('user', function($q) use ($request){
    //                 $q->where('name', 'LIKE', '%' . $request->name . '%');
    //             });
    //         })
    //         ->get();
    // }

    // public function whereClassroomStudent(mixed $id): mixed
    // {
    //     return $this->model->query()
    //         ->whereRelation('classroomStudents', 'id', $id)
    //         ->first();
    // }

    public function getAllCategories(): mixed
    {
        return $this->model->query()->get();
    }

    public function filterByGender(string $gender): mixed
    {
        return $this->model->query()->where('gender', $gender)->get();
    }

    public function filterByMajor(string $major): mixed
    {
        return $this->model->query()->where('major', $major)->get();
    }

    public function filterByLevel(string $level): mixed
    {
        return $this->model->query()->where('level_class', $level)->get();
    }

    public function count(): mixed
    {
        return $this->model->query()->count();
    }
}
