<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Enums\PermissionStatusEnum;
use App\Models\AttendancePermission;
use App\Traits\PaginationTrait;
use Illuminate\Http\Request;

class AttendancePermissionRepository extends BaseRepository implements AttendancePermissionInterface
{
    use PaginationTrait;

    public function __construct(AttendancePermission $attendancePermission)
    {
        $this->model = $attendancePermission;
    }

    public function get(): mixed
    {
        return $this->model
            ->with(['student.user', 'counselor.user'])
            ->latest()
            ->get();
    }

    public function store(array $data): mixed
    {
        return $this->model->create($data);
    }

    public function show(mixed $id): mixed
    {
        return $this->model
            ->with(['student.user', 'counselor.user'])
            ->findOrFail($id);
    }

    public function update(mixed $id, array $data): mixed
    {
        $model = $this->show($id);
        $model->update($data);
        return $model;
    }

    public function delete(mixed $id): mixed
    {
        $model = $this->show($id);
        return $model->delete();
    }

    public function paginate(): mixed
    {
        $data = $this->model
            ->with(['student.user', 'counselor.user'])
            ->latest()
            ->get();

        return $this->paginateCollection($data, 8);
    }

    public function findByStudent(string $studentId, Request $request = null)
    {
        $data = $this->model
            ->with(['student.user', 'counselor.user'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();

        if ($request?->page) {
            return $this->paginateCollection($data, 8);
        }

        return $data;
    }

    public function deleteIfPending(string $id, string $studentId)
    {
        $permission = $this->model
            ->where('id', $id)
            ->where('student_id', $studentId)
            ->where('status', PermissionStatusEnum::PENDING)
            ->firstOrFail();

        return $permission->delete();
    }

    public function getPendingPermissions()
    {
        return $this->model
            ->with(['student.user', 'counselor.user'])
            ->where('status', PermissionStatusEnum::PENDING)
            ->latest()
            ->get();
    }

    public function approvePermission(string $id, string $counselorId): AttendancePermission
    {
        $permission = $this->show($id);

        $permission->update([
            'status' => PermissionStatusEnum::APPROVED->value,
            'counselor_id' => $counselorId,
            'verified_at' => now(),
        ]);

        return $permission->load(['student.user', 'counselor.user']);
    }

    public function rejectPermission(string $id, string $counselorId): AttendancePermission
    {
        $permission = $this->show($id);

        $permission->update([
            'status' => PermissionStatusEnum::REJECTED->value,
            'counselor_id' => $counselorId,
            'verified_at' => now(),
        ]);

        return $permission->load(['student.user', 'counselor.user']);
    }

    public function searchByCounselor(Request $request)
    {
        $query = $this->model
            ->with(['student.user', 'counselor.user'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when(
                $request->start_date && $request->end_date,
                fn($q) => $q->whereBetween('start_date', [$request->start_date, $request->end_date])
            )
            ->latest()
            ->get();

        return $this->paginateCollection($query, 8);
    }
}
