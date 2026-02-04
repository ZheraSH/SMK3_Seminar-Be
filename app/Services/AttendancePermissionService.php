<?php

namespace App\Services;

use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Enums\PermissionStatusEnum;
use App\Enums\UploadDiskEnum;
use Illuminate\Http\Request;
use App\Models\User;
use App\Traits\UploadTrait;

class AttendancePermissionService
{
    use UploadTrait;

    private AttendancePermissionRepository $attendancePermissionRepository;

    public function __construct(AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function studentIndex(User $user, Request $request)
    {
        $studentId = $user->student->id;
        $status = $request->query('status');

        if ($status) {
            return $this->attendancePermissionRepository->getByStatus(
                $studentId,
                PermissionStatusEnum::from($status)->value,
                $request
            );
        }

        return $this->attendancePermissionRepository->getWithConditionalPagination($studentId, $request);
    }

    public function store(User $user, Request $request)
    {
        $data = $request->validated();
        $data['student_id'] = $user->student->id;
        $data['status'] = PermissionStatusEnum::PENDING->value;

        if ($request->hasFile('proof')) {
            $data['proof'] = $this->upload(
                UploadDiskEnum::PROOF->value,
                $request->file('proof')
            );
        }

        return $this->attendancePermissionRepository->store($data);
    }

    public function delete(string $id, User $user): bool
    {
        $studentId = $user->student->id;
        $permission = $this->attendancePermissionRepository
            ->findByIdAndStudent($id, $studentId);

        if (! $permission) {
            throw new \Exception('Data izin tidak ditemukan');
        }

        if ($permission->status !== PermissionStatusEnum::PENDING->value) {
            throw new \Exception('Izin yang sudah diproses tidak dapat dihapus');
        }

        return $this->attendancePermissionRepository->delete($permission->id);
    }

    public function show(string $id)
    {
        return $this->attendancePermissionRepository->show($id);
    }

    public function counselorIndex(User $user, Request $request)
    {
        return $this->attendancePermissionRepository->getAllForCounselor($request);
    }

    public function getPending(User $user, Request $request)
    {
        return $this->attendancePermissionRepository->getPending();
    }

    public function approve(string $id, User $user)
    {
        $counselorId = $user->employee->id;
        return $this->attendancePermissionRepository->approve($id, $counselorId);
    }

    public function reject(string $id, User $user)
    {
        $counselorId = $user->employee->id;
        return $this->attendancePermissionRepository->reject($id, $counselorId);
    }
}
