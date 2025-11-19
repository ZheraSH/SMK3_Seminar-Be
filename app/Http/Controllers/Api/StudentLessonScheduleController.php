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
        $classroom = DB::table('classroom_students')
            ->where('student_id', $student->id)
            ->first();

        if (!$classroom) {
            return response()->json([
                'message' => 'Student does not belong to any classroom'
            ], 404);
        }

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
