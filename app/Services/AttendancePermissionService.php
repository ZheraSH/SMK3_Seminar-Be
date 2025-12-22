<?php

namespace App\Services;

use App\Contracts\Repositories\AttendancePermissionRepository;
use App\Enums\PermissionStatusEnum;
use App\Enums\UploadDiskEnum;
use App\Traits\UploadTrait;

class AttendancePermissionService
{
    use UploadTrait;

    private AttendancePermissionRepository $attendancePermissionRepository;
    
    public function __construct(AttendancePermissionRepository $attendancePermissionRepository)
    {
        $this->attendancePermissionRepository = $attendancePermissionRepository;
    }

    public function studentIndex(string $studentId, ?string $status = null)
    {
        if ($status) {
            return $this->attendancePermissionRepository->getByStatus(
                $studentId,
                PermissionStatusEnum::from($status)->value
            );
        }

        return $this->attendancePermissionRepository->getWithConditionalPagination($studentId);
    }

    public function store($request)
    {
        $data = $request->validated();
        $data['student_id'] = auth()->user()->student->id;
        $data['status'] = PermissionStatusEnum::PENDING->value;

        if ($request->hasFile('proof')) {
            $data['proof'] = $this->upload(
                UploadDiskEnum::PROOF->value,
                $request->file('proof')
            );
        }

        return $this->attendancePermissionRepository->store($data);
    }

    public function delete(string $id, string $studentId): bool
    {
        $permission = $this->attendancePermissionRepository
            ->findByIdAndStudent($id, $studentId);

        if (! $permission) {
            throw new \Exception('Data izin tidak ditemukan');
        }

        if ($permission->status !== PermissionStatusEnum::PENDING->value) {
            throw new \Exception('Izin yang sudah diproses tidak dapat dihapus');
        }

        return $this->attendancePermissionRepository->delete($permission);
    }

    public function show(string $id)
    {
        return $this->attendancePermissionRepository->show($id);
    }

    public function counselorIndex()
    {
        return $this->attendancePermissionRepository->getAllForCounselor();
    }

    public function getPending()
    {
        return $this->attendancePermissionRepository->getPending();
    }

    public function approve(string $id, string $counselorId)
    {
        return $this->attendancePermissionRepository->approve($id, $counselorId);
    }

    public function reject(string $id, string $counselorId)
    {
        return $this->attendancePermissionRepository->reject($id, $counselorId);
    }
}