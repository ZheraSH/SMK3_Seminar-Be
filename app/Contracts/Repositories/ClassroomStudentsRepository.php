<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Models\ClassroomStudents;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class ClassroomStudentsRepository extends BaseRepository implements ClassroomStudentsInterface
{
    use PaginationTrait;

    public function __construct(ClassroomStudents $classroomStudents)
    {
        $this->model = $classroomStudents;
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

    public function paginate(): mixed
    {
        return $this->model->query()
            ->with(['major', 'levelClass', 'schoolYear', 'teacher.user'])
            ->latest()
            ->paginate(8);
    }

    public function search(Request $request, int $pagination = 8): mixed
    {
        return $this->model->query()
            ->with(['student.user', 'classroom'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('student.user', function ($sub) use ($request) {
                        $sub->where('name', 'LIKE', '%' . $request->search . '%');
                    })
                    ->orWhereHas('student', function ($sub) use ($request) {
                        $sub->where('nisn', 'LIKE', '%' . $request->search . '%');
                    });
                });
            })
            ->latest()
            ->paginate($pagination);
    }
    
    public function count(): mixed
    {
        return $this->model->query()->count();
    }
}
