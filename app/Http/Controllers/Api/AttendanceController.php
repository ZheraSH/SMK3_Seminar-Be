<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->attendanceService->search($request);

            if ($request->has('page')) {
                return ResponseHelper::pagination($data, 'Data absensi berhasil diambil');
            }

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function store(StoreAttendanceRequest $request)
    {
        try {
            $data = $this->attendanceService->store($request);

            return ResponseHelper::success(
                new AttendanceResource($data),
                'Absensi berhasil dicatat',
                201
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->attendanceService->show($id);

            return ResponseHelper::success(
                new AttendanceResource($data),
                'Detail absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::notFound('Data absensi tidak ditemukan');
        }
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        try {
            $data = $this->attendanceService->update($attendance, $request);

            return ResponseHelper::success(
                new AttendanceResource($data),
                'Absensi berhasil diperbarui'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function destroy(Attendance $attendance)
    {
        try {
            $this->attendanceService->delete($attendance);

            return ResponseHelper::success(
                null,
                'Absensi berhasil dihapus'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 400, $th->getMessage());
        }
    }

    public function getByClassroom(Request $request, string $classroomId)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $data = $this->attendanceService->getByClassroomAndDate($classroomId, $request->date);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getStudentMonthly(Request $request, string $studentId)
    {
        try {
            $request->validate([
                'month' => 'required|integer|between:1,12',
                'year'  => 'required|integer|min:2020'
            ]);

            $data = $this->attendanceService->getStudentMonthlyAttendance($studentId, $request->month, $request->year);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi bulanan siswa berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getTodayByStudent(string $studentId)
    {
        try {
            $data = $this->attendanceService->getTodayByStudent($studentId);

            return ResponseHelper::success(
                $data ? new AttendanceResource($data) : null,
                'Data absensi hari ini berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }

    public function getByDate(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date'
            ]);

            $data = $this->attendanceService->getByDate($request->date);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi berdasarkan tanggal berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getCode() ?: 500, $th->getMessage());
        }
    }
}