<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentLessonScheduleService;
use App\Helpers\ResponseHelper;
use App\Helpers\SemesterHelper;
use Illuminate\Http\Request;
use Throwable;

class StudentLessonScheduleController extends Controller
{
    public function __construct(
        private StudentLessonScheduleService $scheduleService
    ) {}

    public function getSchedule(Request $request)
    {
        try {
            $student = $request->user()->student;
            if (!$student) {
                return ResponseHelper::error('Data siswa tidak ditemukan', 404);
            }

            $day = $request->query('day');
            $data = $this->scheduleService->getSchedule($student->id, $day);

            // Ambil classroom melalui relasi classroomStudents
            $classroomStudent = $student->classroomStudents()
                ->with('classroom')
                ->where('status', 'active') // jika tidak ada field ini, hapus saja
                ->first();

            $className = $classroomStudent?->classroom?->name ?? 'Kelas Tidak Ditemukan';

            $semester = SemesterHelper::getSemesterLabel();
            $schoolYear = SemesterHelper::getSchoolYear();

            return ResponseHelper::success([
                'kelas'         => $className,
                'semester'      => $semester,
                'tahun_ajaran'  => $schoolYear,
                'hari'          => $this->scheduleService->getDayName($day),
                'jadwal'        => $data,
            ], 'Jadwal pelajaran berhasil diambil');
        } catch (Throwable $e) {
            return ResponseHelper::error('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }
}
