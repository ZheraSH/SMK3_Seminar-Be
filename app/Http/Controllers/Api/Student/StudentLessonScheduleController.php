<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\ClassroomStudents;
use App\Helpers\SemesterHelper;
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

            $activeClassroom = ClassroomStudents::where('student_id', $student->id)
                ->where('status', 'active')
                ->join('classrooms', 'classrooms.id', '=', 'classroom_students.classroom_id')
                ->join('school_years', 'school_years.id', '=', 'classrooms.school_year_id')
                ->select(
                    'classrooms.id',
                    'classrooms.name as classroom_name',
                    'school_years.name as school_year_name'
                )
                ->first();

            if (!$activeClassroom) {
                return ResponseHelper::error('Siswa tidak memiliki kelas aktif', 404);
            }

            $schoolYearActive = $activeClassroom->school_year_name;

            $semester = SemesterHelper::getSemester()['semester'];

          $displayYear = $schoolYearActive;


            $day = $request->query('day');
            $scheduleData = $this->scheduleService->getSchedule($student->id, $day);
            $dayName = $this->scheduleService->getDayName($day);

            return ResponseHelper::success([
                'kelas' => $activeClassroom->classroom_name,
                'semester' => $semester,
                'tahun_ajaran' => $displayYear,
                'hari' => $dayName,
                'jadwal' => $scheduleData
            ], 'Jadwal pelajaran berhasil diambil');

        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal mengambil jadwal: ' . $e->getMessage(), 500);
        }
    }
}
