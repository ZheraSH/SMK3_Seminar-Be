<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ClassroomInterface;
use App\Models\Classroom;
use App\Models\ClassroomStudents;
use App\Enums\StudentStatusEnum;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ClassroomRepository extends BaseRepository implements ClassroomInterface
{
    use PaginationTrait;
    public function __construct(Classroom $classroom)
    {
        $this->model = $classroom;
    }

    public function get(): mixed
    {
        return $this->model->query()
            ->with(['major', 'levelClass', 'schoolYear', 'teacher.user', 'lessonSchedules'])
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
                'major', 
                'levelClass', 
                'schoolYear', 
                'teacher.user', 
                'lessonSchedules.lessonHour',
                'lessonSchedules.subject',
                'lessonSchedules.employee.user',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value)
                          ->with(['student.user', 'student.religion']);
                }
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
            ->with(['major', 'levelClass', 'schoolYear', 'teacher.user', 'lessonSchedules'])
            ->latest()
            ->paginate(9);
    }

    public function search(Request $request, int $pagination = 9): mixed
    {
        return $this->model->query()
            ->with(['major', 'levelClass', 'schoolYear', 'teacher.user', 'lessonSchedules'])
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%')
                        ->orWhereHas('major', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->search . '%');
                        })
                        ->orWhereHas('levelClass', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->search . '%');
                        })
                        ->orWhereHas('teacher.user', function ($sub) use ($request) {
                            $sub->where('name', 'LIKE', '%' . $request->search . '%');
                        });
                });
            })
            ->when($request->major, function ($query) use ($request) {
                $query->whereHas('major', function ($q) use ($request) {
                    $q->where('name', $request->major);
                });
            })
            ->when($request->level_class, function ($query) use ($request) {
                $query->whereHas('levelClass', function ($q) use ($request) {
                    $q->where('name', $request->level_class);
                });
            })
            ->when($request->school_year, function ($query) use ($request) {
                $query->whereHas('schoolYear', function($q) use ($request){
                    $q->where('name', $request->school_year);
                });
            })
            ->latest()
            ->paginate($pagination);
    }

    public function count(): mixed
    {
        return $this->model->query()->count();
    }

    public function addStudentsToClassroom(string $classroomId, array $studentIds): Classroom
    {
        return DB::transaction(function () use ($classroomId, $studentIds) {
            $classroom = $this->model->findOrFail($classroomId);

            foreach ($studentIds as $studentId) {
                ClassroomStudents::updateOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => StudentStatusEnum::ACTIVE->value
                    ]
                );
            }

            return $classroom->fresh(['classroomStudents.student.user', 'classroomStudents.student.religion']);
        });
    }

    public function removeStudentFromClassroom(string $classroomId, string $studentId): Classroom
    {
        $classroom = $this->model->findOrFail($classroomId);

        ClassroomStudents::where('classroom_id', $classroom->id)
            ->where('student_id', $studentId)
            ->delete();

        return $classroom->fresh(['classroomStudents.student.user', 'classroomStudents.student.religion']);
    }

    public function syncClassroomStudents(string $classroomId, array $studentIds): Classroom
    {
        return DB::transaction(function () use ($classroomId, $studentIds) {
            $classroom = $this->model->findOrFail($classroomId);

            ClassroomStudents::where('classroom_id', $classroom->id)
                ->whereNotIn('student_id', $studentIds)
                ->delete();

            foreach ($studentIds as $studentId) {
                ClassroomStudents::where('student_id', $studentId)
                    ->where('classroom_id', '!=', $classroom->id)
                    ->where('status', '!=', StudentStatusEnum::GRADUATED->value)
                    ->delete();
            }

            foreach ($studentIds as $studentId) {
                ClassroomStudents::updateOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => StudentStatusEnum::ACTIVE->value
                    ]
                );
            }

            return $classroom->fresh(['classroomStudents.student.user', 'classroomStudents.student.religion']);
        });
    }

    public function getActiveStudents(string $classroomId): Collection
    {
        $classroom = $this->model->findOrFail($classroomId);
        
        return $classroom->classroomStudents()
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with('student.user')
            ->get();
    }

    public function showWithActiveStudents(mixed $id): mixed
    {
        return $this->model->query()
            ->with([
                'major', 
                'levelClass', 
                'schoolYear', 
                'teacher.user', 
                'lessonSchedules',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value)
                          ->with('student.user');
                }
            ])
            ->findOrFail($id);
    }

    public function countActiveStudents(string $classroomId): int
    {
        return ClassroomStudents::where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }

    public function getClassroomsByStudent(string $studentId): Collection
    {
        return $this->model->query()
            ->whereHas('classroomStudents', function($query) use ($studentId) {
                $query->where('student_id', $studentId)
                      ->where('status', StudentStatusEnum::ACTIVE->value);
            })
            ->with(['major', 'levelClass', 'schoolYear', 'teacher.user', 'lessonSchedules'])
            ->get();
    }

    public function getAvailableStudents(string $classroomId, string $search = null, int $limit = 10): Collection
    {
        $query = \App\Models\Student::with(['user'])
            ->whereDoesntHave('classroomStudents', function($q) {
                $q->where('status', StudentStatusEnum::ACTIVE->value);
            });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name','like','%'. $search .'%');
                })->orWhere('nisn','like','%'. $search .'%');
            });
        }

        return $query->limit($limit)->get();
    }

    public function graduateClass(string $classroomId): void
    {
        $classroom = $this->model->findOrFail($classroomId);
        
        if ($classroom->levelClass->name === 'XII') {
            ClassroomStudents::where('classroom_id', $classroomId)
                ->update(['status' => StudentStatusEnum::GRADUATED->value]);
        }
    }
}