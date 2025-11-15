<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\StudentInterface;
use App\Enums\StudentStatusEnum;
use App\Models\Student;
use App\Traits\PaginationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StudentRepository extends BaseRepository implements StudentInterface
{
    use PaginationTrait;
    
    public function __construct(Student $student)
    {
        $this->model = $student;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->with([
                'user', 
                'religion', 
                'rfid',
                'classroomStudents.classroom.major',
                'classroomStudents.classroom.levelClass',
                'classroomStudents.classroom.schoolYear'
            ])
            ->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model->query()
            ->with([
                'user', 
                'religion', 
                'rfid',
                'classroomStudents.classroom.major',
                'classroomStudents.classroom.levelClass',
                'classroomStudents.classroom.schoolYear'
            ])
            ->findOrFail($id);
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
        ->with([
            'user', 
            'religion', 
            'rfid',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.levelClass',
            'classroomStudents.classroom.schoolYear'
        ])
        ->latest()
        ->paginate(8);
}

public function search(Request $request, int $pagination = 8): mixed
{
    return $this->model->query()
        ->with([
            'user', 
            'religion', 
            'rfid',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.levelClass',
            'classroomStudents.classroom.schoolYear'
        ])
        ->when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($sub) use ($request) {
                    $sub->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->orWhere('nisn', 'LIKE', '%' . $request->search . '%')
                ->orWhereHas('classroomStudents.classroom', function ($sub) use ($request) {
                    $sub->where('name', 'LIKE', '%' . $request->search . '%');
                });
            });
        })
        ->when($request->gender, function ($query) use ($request) {
            $genders = explode(',', $request->gender);
            $query->whereIn('gender', $genders);
        })
        ->when($request->major, function ($query) use ($request) {
            $majorNames = explode(',', $request->major);
            $query->whereHas('classroomStudents.classroom.major', function ($q) use ($majorNames) {
                $q->whereIn('name', $majorNames); // ✅ Konsisten dengan Employee
            });
        })
        ->when($request->level_class, function ($query) use ($request) {
            $levelClassNames = explode(',', $request->level_class);
            $query->whereHas('classroomStudents.classroom.levelClass', function ($q) use ($levelClassNames) {
                $q->whereIn('name', $levelClassNames);
            });
        })
        ->when($request->status, function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->latest()
        ->paginate($pagination);
}
    
    public function count(): mixed
    {
        return $this->model->query()->count();
    }

    public function countActiveStudents(): mixed
    {
        return $this->model->query()
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }

    public function showWithActiveClassroom(mixed $id): mixed
    {
        return $this->model->query()
            ->with([
                'user', 
                'religion',
                'rfid',
                'classroomStudents.classroom.major',
                'classroomStudents.classroom.levelClass', 
                'classroomStudents.classroom.schoolYear'
            ])
            ->findOrFail($id);
    }

    public function getWithActiveClassrooms(): Collection
    {
        return $this->model->query()
            ->with([
                'user', 
                'religion',
                'rfid',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value)
                          ->with(['classroom.major', 'classroom.levelClass', 'classroom.schoolYear']);
                }
            ])
            ->get();
    }

    public function getActiveStudents(): mixed
    {
        return $this->model->query()
            ->with(['user', 'religion', 'rfid'])
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->get();
    }
}