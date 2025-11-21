<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendancePermissionInterface;
use App\Enums\PermissionStatusEnum;
use App\Http\Requests\StoreAttendancePermissionRequest;
use App\Models\AttendancePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $student = auth()->user()->student;
        if (!$student) {
            throw new \Exception('Akun ini bukan siswa', 403);
        }

        $data['student_id'] = $student->id;
        $data['status'] = PermissionStatusEnum::PENDING->value;

        if ($request->hasFile('proof')) {
            $data['proof'] = $request->file('proof')->store('attendance-permissions', 'public');
        }

        return $this->attendancePermission->store($data);
    }

    public function approvePermission(string $id)
    {
        $counselorId = auth()->user()->employee->id;

        return $this->attendancePermission->approvePermission($id, $counselorId);
    }

    public function rejectPermission(string $id)
    {
        $counselorId = auth()->user()->employee->id;

        return $this->attendancePermission->rejectPermission($id, $counselorId);
    }

    public function getPendingPermissions()
    {
        return $this->attendancePermission->getPendingPermissions();
    }

    public function deleteStudentPermission(string $id, string $studentId): bool
    {
        return $this->attendancePermission->deleteIfPending($id, $studentId);
    }

    public function getCounselorPermissions(Request $request)
    {
        return $this->attendancePermission->searchByCounselor($request);
    }

    public function getStudentPermissions(string $studentId, Request $request = null)
    {
        return $this->attendancePermission
            ->findByStudent($studentId, $request)
            ->load(['student.user', 'counselor.user']);
    }

    public function getPermissionDetail(string $id)
    {
        return $this->attendancePermission
            ->show($id)
            ->load(['student.user', 'counselor.user']);
    }

    public function deletePermission(AttendancePermission $permission): bool
    {
        if ($permission->status !== PermissionStatusEnum::PENDING) {
            throw new \Exception('Izin yang sudah diverifikasi tidak dapat dihapus', 400);
        }

        if ($permission->proof) {
            Storage::disk('public')->delete($permission->proof);
        }

        return $this->attendancePermission->delete($permission->id);
    }
}
