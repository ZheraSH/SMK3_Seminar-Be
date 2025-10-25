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

    // public function search(Request $request, int $pagination = 8): mixed
    // {
    //     return $this->model->query()
    //         ->when($request->search, function ($query) use ($request) {
    //             $query->where(function ($q) use ($request) {
    //                 $q->whereHas('user', function ($sub) use ($request) {
    //                     $sub->where('name', 'LIKE', '%' . $request->search . '%');
    //                 })
    //                 ->orWhere('nisn', 'LIKE', '%' . $request->search . '%')
    //                 ->orWhereHas('classroomStudents.classroom', function ($sub) use ($request) {
    //                     $sub->where('name', 'LIKE', '%' . $request->search . '%');
    //                 });
    //             });
    //         })
    //         ->when($request->gender, function ($query) use ($request) {
    //             $query->where('gender', $request->gender);
    //         })
    //         ->when($request->major_id, function ($query) use ($request) {
    //             $query->where('major_id', $request->major_id);
    //         })
    //         ->when($request->level_class, function ($query) use ($request) {
    //             $query->whereHas('level_class', function ($q) use ($request) {
    //                 $q->where('level_class', $request->level_class);
    //             });
    //         })
    //         ->latest()
    //         ->paginate($pagination);
    // }
    
    public function search(Request $request, int $pagination = 8): mixed
{
    return $this->model->query()
        ->when($request->search, function ($query) use ($request) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%');
            })
            ->orWhere('nisn', 'LIKE', '%' . $request->search . '%');
        })
        ->when($request->gender, function ($query) use ($request) {
            $query->where('gender', $request->gender);
        })
        // untuk sementara skip major_id & level_class
        ->latest()
        ->paginate($pagination);
}


    public function count(): mixed
    {
        return $this->model->query()->count();
    }
}
