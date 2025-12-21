<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\StudentInterface;
use App\Contracts\Repositories\BaseRepository;
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

    protected function baseQuery()
    {
        return $this->model->query()->with([
            'user',
            'religion',
            'rfid',
            'classroomStudents.classroom.major',
            'classroomStudents.classroom.schoolYear',
        ]);
    }
    public function get(): Collection
    {
        return $this->baseQuery()->get();
    }

    public function store(array $data): Student
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): Student
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->show($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->show($id)->delete();
    }

    public function paginate(int $perPage = 15): mixed
    {
        return $this->baseQuery()
            ->latest()
            ->paginate($perPage);
    }

    public function search(Request $request, int $pagination = 15): mixed
    {
        return $this->baseQuery()
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) =>
                        $u->where('name', 'LIKE', "%{$search}%")
                    )
                    ->orWhere('nisn', 'LIKE', "%{$search}%")
                    ->orWhereHas('classroomStudents.classroom', fn ($c) =>
                        $c->where('name', 'LIKE', "%{$search}%")
                    )
                    ->orWhereHas('classroomStudents.classroom.major', fn ($m) =>
                        $m->where('name', 'LIKE', "%{$search}%")
                    )
                    ->orWhereHas('classroomStudents.classroom.schoolYear', fn ($sy) =>
                        $sy->where('name', 'LIKE', "%{$search}%")
                    );
                });
            })
            ->when($request->classroom, fn ($q) =>
                $q->whereHas('classroomStudents.classroom', fn ($c) =>
                    $c->whereIn('name', explode(',', $request->classroom))
                )
            )
            ->when($request->major, fn ($q) =>
                $q->whereHas('classroomStudents.classroom.major', fn ($m) =>
                    $m->whereIn('name', explode(',', $request->major))
                )
            )
            ->when($request->school_year, fn ($q) =>
                $q->whereHas('classroomStudents.classroom.schoolYear', fn ($sy) =>
                    $sy->whereIn('name', explode(',', $request->school_year))
                )
            )
            ->latest()
            ->paginate($pagination);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    //Student
    public function getStudentActiveClassroom(string $studentId)
    {
        $classroomStudent = $this->model
            ->where('id', $studentId)
            ->whereHas('classroomStudents', function ($q) {
                $q->where('status', 'active');
            })
            ->with([
                'classroomStudents' => function ($q) {
                    $q->where('status', 'active')
                      ->with([
                          'classroom' => function ($c) {
                              $c->with([
                                  'major',
                                  'schoolYear',
                              ]);
                          }
                      ]);
                }
            ])
            ->firstOrFail()
            ->classroomStudents
            ->first();

        return $classroomStudent->classroom;
    }

    public function getClassroomInfo(string $studentId, int $perPage = 12): array
    {
        $student = $this->model
            ->with([
                'user:id,name',
                'classroomStudents' => function ($q) {
                    $q->where('status', 'active')
                      ->with([
                          'classroom' => function ($c) {
                              $c->with([
                                  'major',
                                  'schoolYear',
                                  'homeroomTeacher.user:id,name',
                              ])
                              ->withCount([
                                  'classroomStudents as classroom_students_count' => function ($cs) {
                                      $cs->where('status', 'active');
                                  }
                              ]);
                          }
                      ]);
                }
            ])
            ->findOrFail($studentId);

        $classroomStudent = $student->classroomStudents->first();

        if (!$classroomStudent || !$classroomStudent->classroom) {
            throw new \Exception('Student is not assigned to any active classroom', 404);
        }

        $classroom = $classroomStudent->classroom;

        $classmates = $classroom->classroomStudents()
            ->where('status', 'active')
            ->where('student_id', '!=', $student->id)
            ->with([
                'student:id,nisn,user_id,image',
                'student.user:id,name'
            ])
            ->paginate($perPage);

        return [
            'student'    => $student,
            'classroom'  => $classroom,
            'classmates' => $classmates
        ];
    }    

    //Student Close
}