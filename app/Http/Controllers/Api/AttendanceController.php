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
                return ResponseHelper::pagination($data, AttendanceResource::class, 'Data absensi berhasil diambil');
            }

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
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
            return ResponseHelper::error($th->getMessage() ?: 'Bad Request',$th->getCode() >= 400 ? $th->getCode() : 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->attendanceService->show($id);

            if (!$data) return ResponseHelper::notFound('Data absensi tidak ditemukan');

            return ResponseHelper::success(
                new AttendanceResource($data),
                'Detail absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage() ?: 'Internal Server Error',$th->getCode() >= 400 ? $th->getCode() : 500);
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
            return ResponseHelper::error($th->getMessage() ?: 'Bad Request',$th->getCode() >= 400 ? $th->getCode() : 400);
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
            return ResponseHelper::error($th->getMessage() ?: 'Bad Request',$th->getCode() >= 400 ? $th->getCode() : 400);
        }
    }

    public function getByClassroom(Request $request, string $classroomId)
    {
        try {
            $date = $this->attendanceService->validateDate($request->all());
            $data = $this->attendanceService->getByClassroomAndDate($classroomId, $date);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() >= 400 ? $th->getCode() : 400);
        }
    }

    public function getStudentMonthly(Request $request, string $studentId)
    {
        try {
            [$month, $year] = $this->attendanceService->validateMonthYear($request->all());
            $data = $this->attendanceService->getStudentMonthlyAttendance($studentId, $month, $year);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi bulanan siswa berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() >= 400 ? $th->getCode() : 400);
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
            return ResponseHelper::error($th->getMessage(),$th->getCode() >= 400 ? $th->getCode() : 400);
        }
    }

    public function getByDate(Request $request)
    {
        try {
            $date = $this->attendanceService->validateDate($request->all());
            $data = $this->attendanceService->getByDate($date);

            return ResponseHelper::success(
                AttendanceResource::collection($data),
                'Data absensi berdasarkan tanggal berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(),$th->getCode() >= 400 ? $th->getCode() : 400);
        }
    }
}