<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendancePermissionRequest;
use App\Http\Resources\AttendancePermissionDetailResource;
use App\Http\Resources\AttendancePermissionResource;
use App\Services\AttendancePermissionService;
use Illuminate\Http\Request;

class StudentAttendancePermissionController extends Controller
{
    private AttendancePermissionService $attendancePermissionService;

    public function __construct(AttendancePermissionService $attendancePermissionService)
    {
        $this->attendancePermissionService = $attendancePermissionService;
    }
    
    public function index(Request $request)
    {
        try {
            $studentId = auth()->user()->student->id;
            $data = $this->attendancePermissionService->getStudentPermissions($studentId, $request);
            
            return ResponseHelper::pagination(
                $data, 
                AttendancePermissionResource::class, 
                'Data izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreAttendancePermissionRequest $request)
    {
        try {
            $data = $this->attendancePermissionService->store($request);

            return ResponseHelper::success(
                new AttendancePermissionResource($data),
                'Izin berhasil diajukan',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->attendancePermissionService->getPermissionDetail($id);

            return ResponseHelper::success(
                new AttendancePermissionDetailResource($data),
                'Detail izin berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 404);
        }
    }

    public function destroy(string $id)
    {
        try {
            $studentId = auth()->user()->student->id;
            $this->attendancePermissionService->deleteStudentPermission($id, $studentId);

            return ResponseHelper::success(
                null,
                'Izin berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}