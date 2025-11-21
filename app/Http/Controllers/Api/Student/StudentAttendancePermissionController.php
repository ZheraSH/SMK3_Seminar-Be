<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendancePermissionRequest;
use App\Http\Resources\AttendancePermissionDetailResource;
use App\Http\Resources\AttendancePermissionResource;
use App\Services\AttendancePermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StudentAttendancePermissionController extends Controller
{
    private AttendancePermissionService $AttendancePermissionService;

    public function __construct(AttendancePermissionService $AttendancePermissionService)
    {
        $this->AttendancePermissionService = $AttendancePermissionService;
    }
    
    public function index(Request $request): JsonResponse
    {
        try {
            $studentId = auth()->user()->student->id;
            $permissions = $this->AttendancePermissionService->getStudentPermissions($studentId, $request);
            
            if ($request->has('page')) {
                return ResponseHelper::pagination($permissions, AttendancePermissionResource::class, 'Data izin berhasil diambil');
            }
            
            return ResponseHelper::success(
                AttendancePermissionResource::collection($permissions),
                'Data izin berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function store(StoreAttendancePermissionRequest $request): JsonResponse
    {
        try {
            $permission = $this->AttendancePermissionService->store($request);

            return ResponseHelper::success(
                new AttendancePermissionResource($permission),
                'Izin berhasil diajukan',
                201
            );
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $permission = $this->AttendancePermissionService->getPermissionDetail($id);

            return ResponseHelper::success(
                new AttendancePermissionDetailResource($permission),
                'Detail izin berhasil diambil'
            );
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $studentId = auth()->user()->student->id;
            $this->AttendancePermissionService->deleteStudentPermission($id, $studentId);

            return ResponseHelper::success(null, 'Izin berhasil dihapus');
        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}