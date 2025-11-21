<?php

namespace App\Services;

use App\Models\LessonSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StudentLessonScheduleResource;

class StudentScheduleService
{
    private array $dayMapping = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat'
    ];

    private array $reverseDayMapping = [
        'senin' => 'monday',
        'selasa' => 'tuesday',
        'rabu' => 'wednesday',
        'kamis' => 'thursday',
        'jumat' => 'friday'
    ];

    public function getStudentWithActiveClassroom()
    {
        $student = auth()->user()->student;

        if (!$student) {
            return $this->formatError('Data siswa tidak ditemukan', 404);
        }

        $classroom = DB::table('classroom_students')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$classroom) {
            return $this->formatError('Siswa tidak tergabung di kelas aktif', 404);
        }

        return $this->formatSuccess(compact('student', 'classroom'));
    }

    public function getAllSchedules($classroomId)
    {
        $schedules = LessonSchedule::where('classroom_id', $classroomId)
            ->with(['subject','employee.user','lessonHour'])
            ->orderByRaw("FIELD(day, 'monday','tuesday','wednesday','thursday','friday')")
            ->orderBy('lesson_hour_id')
            ->get();

        $formatted = [];

        foreach ($this->dayMapping as $eng => $indo) {
            $daySchedules = $schedules->where('day', $eng)
                ->values()
                ->map(fn($i, $idx) => tap($i, fn($x) => $x->number = $idx + 1));

            $formatted[] = [
                'hari' => $indo,
                'jadwal' => StudentLessonScheduleResource::collection($daySchedules)
            ];
        }

        return $this->formatSuccess($formatted);
    }

    public function getSchedulesByDay($classroomId, $day)
    {
        if (!$this->validateDay($day)) {
            return $this->formatError('Hari tidak valid! Gunakan: senin, selasa, rabu, kamis, jumat');
        }

        $eng = $this->reverseDayMapping[strtolower($day)];
        $indo = $this->dayMapping[$eng];

        $schedules = LessonSchedule::where('classroom_id', $classroomId)
            ->where('day', $eng)
            ->with(['subject','employee.user','lessonHour'])
            ->orderBy('lesson_hour_id')
            ->get()
            ->map(fn($i, $idx) => tap($i, fn($x) => $x->number = $idx + 1));

        return $this->formatSuccess([
            'hari' => $indo,
            'jadwal' => StudentLessonScheduleResource::collection($schedules)
        ]);
    }

    public function validateDay($day)
    {
        return array_key_exists(strtolower($day), $this->reverseDayMapping);
    }

    private function formatSuccess($data)
    {
        return [
            'success' => true,
            'data' => $data,
            'error' => null,
            'code' => 200
        ];
    }

    private function formatError($message, $code = 400)
    {
        return [
            'success' => false,
            'data' => null,
            'error' => $message,
            'code' => $code
        ];
    }

    public function formatAllSchedulesResponse($student, $classroom, $schedules)
    {
        return [
            'status' => true,
            'message' => 'Berhasil mengambil jadwal',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'classroom' => $classroom->classroom_id
                ],
                'hari' => $schedules
            ],
            'errors' => null
        ];
    }

    public function formatDailyScheduleResponse($student, $classroom, $schedules)
    {
        return [
            'status' => true,
            'message' => "Berhasil mengambil jadwal hari {$schedules['hari']}",
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'classroom' => $classroom->classroom_id
                ],
                'hari' => $schedules['hari'],
                'jadwal' => $schedules['jadwal']
            ],
            'errors' => null
        ];
    }
}
