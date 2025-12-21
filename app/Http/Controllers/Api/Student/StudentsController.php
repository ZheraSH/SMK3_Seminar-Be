<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\Student\StudentService;
use App\Http\Resources\Student\StudentClassroomInfoResource;
use App\Http\Resources\Student\StudentAttendanceHistoryResource;
use App\Http\Resources\Student\StudentLessonScheduleResource;
use App\Helpers\ResponseHelper;

class StudentsController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function getStudentClassroom()
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->studentService->getClassroomInfo($studentId);

            return ResponseHelper::success(
                new StudentClassroomInfoResource($data),
                'Informasi kelas berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }

    public function getStudentHistory()
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->studentService->getStudentHistory($studentId);

            return ResponseHelper::pagination($data,
            StudentAttendanceHistoryResource::class,
            'Riwayat absensi berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function getStudentSchedule(string $day)
    {
        try {
            $studentId = auth()->user()->student->id;

            $data = $this->studentService->getStudentScheduleByDay($studentId, $day);

            return ResponseHelper::success(
                new StudentLessonScheduleResource($data),
                'Jadwal pelajaran berhasil diambil'
            );
        } catch (\Throwable $th) {
            return ResponseHelper::error($th->getMessage(), $th->getCode() ?: 400);
        }
    }
}