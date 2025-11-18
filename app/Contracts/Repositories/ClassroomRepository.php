<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\ClassroomInterface;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\ClassroomStudents;
use App\Enums\StudentStatusEnum;
use App\Traits\PaginationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ClassroomRepository extends BaseRepository implements ClassroomInterface
{
    use PaginationTrait;
    
    public function __construct(Classroom $classroom)
    {
        $this->model = $classroom;
    }
    private array $defaultRelations = [
        'major',
        'levelClass',
        'schoolYear',
        'teacher.user',
    ];
    
    public function loadRelations($model)
    {
        return $model->load($this->defaultRelations);
    }
    
    public function get(): mixed
    {
        return $this->model->query()
            ->with([
                'major', 
                'levelClass', 
                'schoolYear', 
                'teacher.user', 
                'lessonSchedules',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value);
                }
            ])
            ->get();
    }

    public function store(array $data): mixed
    {
        $model = $this->model->create($data);
        return $this->loadRelations($model);
    }    

    public function show(mixed $id):mixed
    {
        return $this->model->query()
            ->with([
                'major',
                'levelClass',
                'schoolYear',
                'teacher.user',
                'classroomStudents' => function ($q) {
                    $q->with([
                        'student' => function ($s) {
                            $s->with([
                                'user',
                                'rfid',
                            ]);
                        }
                    ]);
                }
            ])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        $model = $this->model->findOrFail($id);
        $model->update($data);
    
        return $this->loadRelations($model);
    }    

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->model->query()
            ->with([
                'major', 
                'levelClass', 
                'schoolYear', 
                'teacher.user', 
                'lessonSchedules',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value);
                }
            ])
            ->latest()
            ->paginate(9);
    }

    public function search(Request $request, int $pagination = 9): mixed
    {
        return $this->model->query()
            ->with([
                'major', 
                'levelClass', 
                'schoolYear', 
                'teacher.user', 
                'lessonSchedules',
                'classroomStudents' => function($query) {
                    $query->where('status', StudentStatusEnum::ACTIVE->value);
                }
            ])
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

            ClassroomStudents::where('classroom_id', $classroom->id)
                ->whereIn('student_id', $studentIds)
                ->update(['status' => StudentStatusEnum::INACTIVE->value]);

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

            return $this->show($classroomId);
        });
    }

    public function removeStudentFromClassroom(string $classroomId, string $studentId): Classroom
    {
        return DB::transaction(function () use ($classroomId, $studentId) {
            $classroom = $this->model->findOrFail($classroomId);

            ClassroomStudents::where('classroom_id', $classroom->id)
                ->where('student_id', $studentId)
                ->update(['status' => StudentStatusEnum::INACTIVE->value]);

            return $this->show($classroomId);
        });
    }

    public function syncClassroomStudents(string $classroomId, array $studentIds): Classroom
    {
        return DB::transaction(function () use ($classroomId, $studentIds) {
            $classroom = $this->model->findOrFail($classroomId);

            ClassroomStudents::where('classroom_id', $classroom->id)
                ->whereNotIn('student_id', $studentIds)
                ->update(['status' => StudentStatusEnum::INACTIVE->value]);

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

            return $this->show($classroomId);
        });
    }

    public function getActiveStudents(string $classroomId): Collection
    {
        $classroom = $this->model->findOrFail($classroomId);
        
        return $classroom->students()
            ->wherePivot('status', StudentStatusEnum::ACTIVE->value)
            ->with(['user', 'religion'])
            ->get();
    }

    public function getAvailableStudents(string $classroomId, string $search = null, int $limit = 10): Collection
    {
        $classroom = $this->model->findOrFail($classroomId);

        $activeStudentIds = ClassroomStudents::where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->pluck('student_id')
            ->toArray();

        $query = Student::with(['user', 'religion'])
            ->whereNotIn('id', $activeStudentIds);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
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

    public function getWithSchedules(): mixed
    {
        return $this->model->query()
            ->with([
                'employee.user',
                'schoolYear', 
                'major',
                'levelClass',
                'classroomStudents' => function($query) {
                    $query->where('status', \App\Enums\StudentStatusEnum::ACTIVE->value)
                          ->with(['student.user']);
                },
                'lessonSchedules.lessonHour',
                'lessonSchedules.subject',
                'lessonSchedules.employee.user'
            ])
            ->get();
    }

    public function getWithSchedulesById(string $id): mixed
    {
        return $this->model->query()
            ->with([
                'employee.user',
                'schoolYear', 
                'major',
                'levelClass',
                'classroomStudents.student.user',
                'lessonSchedules.lessonHour',
                'lessonSchedules.subject',
                'lessonSchedules.employee.user'
            ])
            ->findOrFail($id);
    }
}