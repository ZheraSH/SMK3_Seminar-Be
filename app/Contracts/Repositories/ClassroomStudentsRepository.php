<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ClassroomStudentsInterface;
use App\Enums\StudentStatusEnum;
use App\Models\ClassroomStudents;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function store(array $data): ClassroomStudents
    {
        return $this->model->query()->create($data);
    }

    public function show(mixed $id): ClassroomStudents
    {
        return $this->model->query()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    public function paginate(): LengthAwarePaginator
    {
        return $this->model->query()
            ->with([
                'student.user',
                'student.classroomStudents.classroom.major',
                'student.classroomStudents.classroom.levelClass',
                'student.classroomStudents.classroom.schoolYear',
                'student.rfid',
                'classroom.major',
                'classroom.levelClass',
                'classroom.schoolYear',
                'classroom.teacher.user'
            ])
            ->latest()
            ->paginate(8);
    }

    public function search(Request $request, int $pagination = 8): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with([
                'student.user',
                'student.classroomStudents.classroom.major',
                'student.classroomStudents.classroom.levelClass',
                'student.classroomStudents.classroom.schoolYear',
                'student.rfid',
                'classroom'
            ]);

        if ($request->has('classroom_id') && !empty($request->classroom_id)) {
            $query->where('classroom_id', $request->classroom_id);
        }

        $query->when($request->search, function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('student.user', function ($sub) use ($request) {
                    $sub->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->orWhereHas('student', function ($sub) use ($request) {
                    $sub->where('nisn', 'LIKE', '%' . $request->search . '%');
                });
            });
        });

        return $query->latest()->paginate($pagination);
    }
    
    public function count(): int
    {
        return $this->model->query()->count();
    }

    public function getByStudentId(string $studentId): mixed
    {
        return $this->model->query()
            ->with([
                'student.user',
                'student.classroomStudents.classroom.major',
                'student.classroomStudents.classroom.levelClass',
                'student.classroomStudents.classroom.schoolYear',
                'student.rfid',
                'classroom'
            ])
            ->where('student_id', $studentId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();
    }

    public function getByClassroom(string $classroomId, Request $request = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->query()
            ->with([
                'student.user',
                'classroom.major',
                'classroom.levelClass',
                'classroom.schoolYear',
            ])
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value);

        return $query->get();
    }

    public function getByClassroomPaginated(string $classroomId, Request $request = null): LengthAwarePaginator
    {
        $query = $this->model->query()
            ->with([
                'student.user',
                'student.classroomStudents.classroom.major',
                'student.classroomStudents.classroom.levelClass',
                'student.classroomStudents.classroom.schoolYear',
                'student.rfid',
                'classroom.major',
                'classroom.levelClass',
                'classroom.schoolYear',
                'classroom.teacher.user'
            ])
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value);

        if ($request && $request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('student.user', function ($sub) use ($request) {
                    $sub->where('name', 'LIKE', '%' . $request->search . '%');
                })
                ->orWhereHas('student', function ($sub) use ($request) {
                    $sub->where('nisn', 'LIKE', '%' . $request->search . '%');
                });
            });
        }

        return $query->latest()->paginate(8);
    }

    public function getByStudentAndClassroom(string $studentId, string $classroomId): mixed
    {
        return $this->model->query()
            ->with(['student', 'classroom'])
            ->where('student_id', $studentId)
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();
    }

    public function countActiveByClassroom(string $classroomId): int
    {
        return $this->model->query()
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }
}