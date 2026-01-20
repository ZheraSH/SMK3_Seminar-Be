<?php

namespace App\Http\Controllers\Api\Homeroom_teacher;

use App\Http\Controllers\Controller;
use App\Services\Homeroom_Teacher\HomeroomTeacherService;
use App\Http\Resources\Homeroom_teacher\SummaryClassResource;
use App\Http\Resources\Homeroom_teacher\StudentAttendanceListResource;
use App\Http\Requests\Homeroom_teacher\StudentAttendanceListRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class HomeroomTeachersController extends Controller
{
    private HomeroomTeacherService $service;

    public function __construct(HomeroomTeacherService $service)
    {
        $this->service = $service;
    }

    public function getHeaderClass()
    {
        try {
            $teacher = auth()->user();
            $data = $this->service->getClassroomHeader($teacher);

            return ResponseHelper::success(
                new SummaryClassResource($data),
                'Header kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getStudentAttendanceList(StudentAttendanceListRequest $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            $search = $request->input('search');
            $status = $request->input('status');
            $perPage = $request->input('per_page', 10);

            $data = $this->service->getDailyAttendance($teacher, $date, $search, $status, $perPage);

            return ResponseHelper::success([
                'students' => StudentAttendanceListResource::collection($data['students']),
                'pagination' => $data['pagination'],
            ], 'Daftar kehadiran siswa berhasil diambil');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function generateAttendanceRecap(Request $request)
    {
        try {
            $teacher = $request->user();
            $date = $request->input('date', now()->format('Y-m-d'));

            $recap = $this->service->generateAttendanceRecap($teacher, $date);

            if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                $export = new \App\Exports\AttendanceRecapExport($recap);
                $filename = 'rekap-absensi-' . $recap['classroom']['name'] . '-' . $date . '.xlsx';

                return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
            }

            return ResponseHelper::success($recap, 'Rekap absensi berhasil dibuat');
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}
