<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StudentLessonScheduleController extends Controller
{
    public function index(Student $student)
    {
        // Cari classroom_id dari pivot classroom_students
        $classroom = DB::table('classroom_students')
            ->where('student_id', $student->id)
            ->first();

        // Jika belum punya kelas
        if (!$classroom) {
            return response()->json([
                'message' => 'Student does not belong to any classroom'
            ], 404);
        }

        // Ambil jadwal via classroom_id
        $schedules = DB::table('lesson_schedules')
            ->where('classroom_id', $classroom->classroom_id)
            ->get();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
            ],
            'classroom_id' => $classroom->classroom_id,
            'schedules' => $schedules
        ]);
    }
}
