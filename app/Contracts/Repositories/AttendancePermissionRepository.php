<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceInterface;
use App\Enums\PermissionStatusEnum;
use App\Enums\StudentStatusEnum;
use App\Models\AttendancePermission;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendancePermissionRepository extends BaseRepository implements AttendanceInterface
{
    use PaginationTrait;

    public function __construct(AttendancePermission $attendancePermission)
    {
        $this->model = $attendancePermission;
    }

    protected function baseQuery()
    {
        return $this->model->with(['student.user', 'counselor.user']);
    }

    public function get(string $studentId = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->latest()
            ->paginate(10);
    }

    public function store(array $data): AttendancePermission
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->baseQuery()->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        return $this->show($id)->update($data);
    }

    public function findByIdAndStudent(string $id, ?string $studentId = null): ?AttendancePermission
    {
        return $this->model
            ->where('id', $id)
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->first();
    }

    public function delete(mixed $id): mixed
    {
        return $this->show($id)->delete();
    }

    public function getWithConditionalPagination(string $studentId, Request $request = null): LengthAwarePaginator
    {
        $hasPending = $this->model
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->exists();

        $perPage = $hasPending ? 5 : 10;

        $query = $this->baseQuery()
            ->where('student_id', $studentId)
            ->when($request && $request->start_date, function ($q) use ($request) {
                $q->whereDate('permission_date', '>=', $request->start_date);
            })
            ->when($request && $request->end_date, function ($q) use ($request) {
                $q->whereDate('permission_date', '<=', $request->end_date);
            })
            ->latest();

        if ($hasPending) {
            $query->where('status', '!=', 'pending');
        }

        return $query->paginate($perPage);
    }

    public function getByStatus(string $studentId, string $status, Request $request = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('student_id', $studentId)
            ->where('status', $status)
            ->when($request && $request->start_date, function ($q) use ($request) {
                $q->whereDate('permission_date', '>=', $request->start_date);
            })
            ->when($request && $request->end_date, function ($q) use ($request) {
                $q->whereDate('permission_date', '<=', $request->end_date);
            })
            ->latest()
            ->paginate($status === 'pending' ? 3 : 5);
    }

    public function getPending(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('status', 'pending')
            ->latest()
            ->paginate(5);
    }

    public function getAllForCounselor(Request $request): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('student.user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->classroom, function ($query) use ($request) {
                $classrooms = explode(',', $request->classroom);
                $query->whereHas('student.classroomStudents', function ($q) use ($classrooms) {
                    $q->where('status', StudentStatusEnum::ACTIVE->value)
                        ->whereHas('classroom', function ($c) use ($classrooms) {
                            $c->whereIn('name', $classrooms);
                        });
                });
            })
            ->when($request->type, function ($query) use ($request) {
                $query->where('type', $request->type);
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);
    }

    public function approve(string $id, string $counselorId): AttendancePermission
    {
        $permission = $this->model->findOrFail($id);

        $permission->update([
            'status' => 'approved',
            'counselor_id' => $counselorId,
            'verified_at' => now(),
        ]);

        return $permission->fresh();
    }

    public function reject(string $id, string $counselorId): AttendancePermission
    {
        $permission = $this->model->findOrFail($id);

        $permission->update([
            'status' => 'rejected',
            'counselor_id' => $counselorId,
            'verified_at' => now(),
        ]);

        return $permission->fresh();
    }

    //Student
    public function countApprovedByStudent(string $studentId): int
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('status', PermissionStatusEnum::APPROVED->value)
            ->count();
    }

    //Student Close
}
