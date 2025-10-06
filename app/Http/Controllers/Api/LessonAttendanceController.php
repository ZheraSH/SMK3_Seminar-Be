<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\MarkLessonAttendanceRequest;
use App\Services\Contracts\LessonAttendanceServiceInterface;

class LessonAttendanceController extends Controller
{
    public function __construct(private LessonAttendanceServiceInterface $lessonService) {}

    public function index()
    {
        $data = $this->lessonService->listAll();
        return ResponseHelper::success($data, 'Lesson attendance list retrieved');
    }

    public function mark(MarkLessonAttendanceRequest $request)
    {
        $data = $request->validated();
        $journal = $this->lessonService->mark(
            $data['teacher_journal_id'],
            $data['student_classroom_id'],
            $data['lesson_hour_id'],
            $data['status'],
            $data['date'] ?? null
        );
        return ResponseHelper::success($journal, 'Lesson attendance recorded');
    }
}


