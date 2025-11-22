<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Http\Requests\StoreAttendancePermissionRequest;
use App\Models\AttendancePermission;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AttendancePermissionService
{
    private AttendancePermissionInterface $attendancePermission;

    public function __construct(AttendancePermissionInterface $attendancePermission)
    {
        $this->attendancePermission = $attendancePermission;
    }

    public function store(StoreAttendancePermissionRequest $request): AttendancePermission
    {
        $data = $request->validated();
        $permission = $this->attendancePermission->store($data);
        return $this->attendancePermission->show($permission->id);
    }

    public function getPermissionDetail(string $id): AttendancePermission
    {
        return $this->attendancePermission->show($id);
    }

    public function getStudentPermissions(string $studentId, Request $request): LengthAwarePaginator
    {
        return $this->attendancePermission->findByStudent($studentId, $request);
    }

    public function getCounselorPermissions(Request $request): LengthAwarePaginator
    {
        return $this->attendancePermission->searchByCounselor($request);
    }

    public function getPendingPermissions(): Collection
    {
        return $this->attendancePermission->getPendingPermissions();
    }

    public function deleteStudentPermission(string $id, string $studentId): bool
    {
        return $this->attendancePermission->deleteIfPending($id, $studentId);
    }

    public function approvePermission(string $id, string $counselorId): AttendancePermission
    {
        return $this->attendancePermission->approvePermission($id, $counselorId);
    }

    public function rejectPermission(string $id, string $counselorId): AttendancePermission
    {
        return $this->attendancePermission->rejectPermission($id, $counselorId);
    }

    public function getWithFilter(Request $request): LengthAwarePaginator
    {
        return $this->attendancePermission->searchByCounselor($request);
    }
}