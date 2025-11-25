<?php
namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceHistoryResource;
use App\Services\AttendanceHistoryService;
use Illuminate\Http\Request;

class StudentAttendanceHistoryController extends Controller
{
    private AttendanceHistoryService $historyService;

    public function __construct(AttendanceHistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    public function index(Request $request)
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->historyService->getStudentHistory($studentId);

        return ResponseHelper::pagination(
            $data,
            AttendanceHistoryResource::class,
            'Riwayat absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }
}
