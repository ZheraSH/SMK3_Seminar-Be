<?php

namespace App\Contracts\Repositories\Operator;

use App\Contracts\Interfaces\Operator\ClassroomStudentsInterface;
use App\Contracts\Repositories\BaseRepository;
use App\Enums\StudentStatusEnum;
use App\Models\ClassroomStudents;
use App\Models\Student;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClassroomStudentsRepository extends BaseRepository implements ClassroomStudentsInterface
{
    use PaginationTrait;

    public function __construct(ClassroomStudents $classroomStudents)
    {
        $this->model = $classroomStudents;
    }

    protected function baseQuery()
    {
        return $this->model->query()->with([
            'student.user:id,name',
            'student.rfid:id,student_id,rfid',
            'classroom:id,name',
        ]);
    }

    public function get(): mixed
    {
        return $this->baseQuery()->latest()->get();
    }

    public function store(array $data): ClassroomStudents
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): ClassroomStudents
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): bool
    {
        return $this->model->findOrFail($id)->update($data);
    }

    public function delete(mixed $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function paginate(): mixed
    {
        return $this->baseQuery()->paginate(10);
    }

    public function countByClassroom(string $classroomId): int
    {
        return $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }

    public function getByClassroom(string $classroomId, Request $request = null): mixed
    {
        $query = $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with([
                'student' => function ($q) {
                    $q->select('id', 'nisn', 'gender', 'user_id', 'image')
                        ->with([
                            'user:id,name',
                            'rfid:id,student_id,rfid'
                        ]);
                }
            ]);

        if ($request?->search) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn($u) =>
                        $u->where('name', 'like', "%{$search}%")
                    );
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($request->limit ?? 10);
    }

    public function getAvailableStudents(string $classroomId, ?string $search, int $limit = 10): Collection
    {
        $query = Student::query()
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->whereDoesntHave('classroomStudents', function ($q) {
                $q->where('status', StudentStatusEnum::ACTIVE->value);
            })
            ->with('user:id,name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn($u) =>
                        $u->where('name', 'like', "%{$search}%")
                    );
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function addStudents(string $classroomId, array $studentIds): void
    {
        DB::transaction(function () use ($classroomId, $studentIds) {
            foreach ($studentIds as $studentId) {
                $this->model->updateOrCreate(
                    [
                        'classroom_id' => $classroomId,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => StudentStatusEnum::ACTIVE->value,
                        'active_unique_guard' => $studentId
                    ]
                );
            }
        });
    }

    public function removeStudent(string $classroomId, string $studentId): void
    {
        $this->model
            ->where('classroom_id', $classroomId)
            ->where('student_id', $studentId)
            ->update([
                'status' => StudentStatusEnum::INACTIVE->value,
                'active_unique_guard' => null
            ]);
    }

    public function getActiveByClassroom(string $classroomId): Collection
    {
        return $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->get(['id', 'student_id']);
    }

    public function bulkDeactivate(array $ids, string $now): void
    {
        $this->model->whereIn('id', $ids)->update([
            'status' => StudentStatusEnum::INACTIVE->value,
            'active_unique_guard' => null,
            'updated_at' => $now,
        ]);
    }

    public function bulkUpsertActive(array $rows): void
    {
        $this->model->upsert(
            $rows,
            ['active_unique_guard'],
            ['classroom_id', 'status', 'updated_at']
        );
    }

    public function getActiveStudentIds(string $classroomId): mixed
    {
        return $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->pluck('student_id');
    }

    //Teacher
    public function getByClassroomForAttendance(string $classroomId, ?Request $request = null): LengthAwarePaginator
    {
        $query = $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with([
                'student.user:id,name',
                'student.rfid:id,student_id,rfid'
            ]);

        if ($request?->search) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        fn($u) =>
                        $u->where('name', 'like', "%{$search}%")
                    );
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->limit ?? 20);
    }

    public function getByStudentAndClassroom(string $studentId, string $classroomId): ?ClassroomStudents
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->first();
    }


    public function countActiveByClassroom(string $classroomId): int
    {
        return $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->count();
    }

    //Teacher Close

    //Homeroom Teacher

    public function getByClassroomForDailyAttendance(string $classroomId, ?string $date = null, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with(['student.user', 'student']);

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && $date) {
            if ($status === 'alpha') {
                $query->whereDoesntHave('student.attendanceRfids', function ($q) use ($date) {
                    $q->whereDate('date', $date)
                        ->where('is_final', true);
                });
            } else {
                $query->whereHas('student.attendanceRfids', function ($q) use ($date, $status) {
                    $q->whereDate('date', $date)
                        ->where('status', $status)
                        ->where('is_final', true);
                });
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllByClassroomForAttendanceRecap(string $classroomId, ?string $date = null, ?string $search = null, ?string $status = null): Collection
    {
        $query = $this->model
            ->where('classroom_id', $classroomId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with(['student.user', 'student']);

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && $date) {
            if ($status === 'alpha') {
                $query->whereDoesntHave('student.attendanceRfids', function ($q) use ($date) {
                    $q->whereDate('date', $date);
                });
            } else {
                $query->whereHas('student.attendanceRfids', function ($q) use ($date, $status) {
                    $q->whereDate('date', $date)
                        ->where('status', $status);
                });
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->get();
    }

    //Homeroom Teacher Close

    //Counselor
    public function getActiveStudentClassroom(string $studentId): ?ClassroomStudents
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with(['classroom'])
            ->latest()
            ->first();
    }

    public function getGlobalDailyAttendance(?string $date = null, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model
            ->where('status', StudentStatusEnum::ACTIVE->value)
            ->with(['student.user', 'classroom']);

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && $date) {
            if ($status === 'alpha') {
                $query->whereDoesntHave('student.attendanceRfids', function ($q) use ($date) {
                    $q->whereDate('date', $date)
                        ->where('is_final', true);
                });
            } else {
                $query->whereHas('student.attendanceRfids', function ($q) use ($date, $status) {
                    $q->whereDate('date', $date)
                        ->where('status', $status)
                        ->where('is_final', true);
                });
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    //Counselor Close
}
