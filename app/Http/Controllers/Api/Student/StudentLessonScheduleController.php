<?php

namespace App\Http\Controllers\Api\Student;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\StudentLessonScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentLessonScheduleController extends Controller
{
    public function __construct(
        private StudentLessonScheduleService $scheduleService
    ) {}

    public function getSchedule(Request $request): JsonResponse
    {
        try {
            $student = $request->user()->student;
            
            if (!$student) {
                return ResponseHelper::error('Data siswa tidak ditemukan', 404);
            }

            $day = $request->query('day');
            $scheduleData = $this->scheduleService->getSchedule($student->id, $day);
            $dayName = $this->scheduleService->getDayName($day);

            return ResponseHelper::success([
                'hari' => $dayName,
                'jadwal' => $scheduleData
            ], 'Jadwal pelajaran berhasil diambil');

        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal mengambil jadwal: ' . $e->getMessage(), 500);
        }
    }
}