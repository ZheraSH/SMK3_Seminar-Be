<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentLessonScheduleResource;
use App\Models\LessonSchedule;
use Illuminate\Support\Facades\DB;

class StudentLessonScheduleController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student ?? null;

        if (!$student) {
            return response()->json([
                'status' => false,
                'message' => 'Data siswa tidak ditemukan',
                'data' => null,
                'errors' => null
            ], 404);
        }

        $classroom = DB::table('classroom_students')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->first();

        if (!$classroom) {
            return response()->json([
                'status' => false,
                'message' => 'Siswa tidak tergabung di kelas aktif',
                'data' => null,
                'errors' => null
            ], 404);
        }

        $schedules = LessonSchedule::where('classroom_id', $classroom->classroom_id)
            ->with(['subject', 'classroom', 'employee.user', 'lessonHour'])
            ->orderBy('lesson_hour_id')
            ->get()
            ->map(function ($item, $index) {
                $item->number = $index + 1; // Nomor urut
                return $item;
            });

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengambil jadwal',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->user?->name,
                    'classroom_id' => $classroom->classroom_id
                ],
                'schedules' => StudentLessonScheduleResource::collection($schedules)
            ],
            'errors' => null
        ]);
    }
}
