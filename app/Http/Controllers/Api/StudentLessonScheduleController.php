<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentScheduleService;

class StudentLessonScheduleController extends Controller
{
    private StudentScheduleService $service;

    public function __construct(StudentScheduleService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $student = $this->service->getStudentWithActiveClassroom();
        if (!$student['success']) {
            return response()->json([
                'status' => false,
                'message' => $student['error'],
                'data' => null,
                'errors' => null
            ], $student['code']);
        }

        $all = $this->service->getAllSchedules($student['data']['classroom']->classroom_id);

        return response()->json(
            $this->service->formatAllSchedulesResponse(
                $student['data']['student'],
                $student['data']['classroom'],
                $all['data']
            ), 200
        );
    }

    public function getByDay($day)
    {
        $student = $this->service->getStudentWithActiveClassroom();
        if (!$student['success']) {
            return response()->json([
                'status' => false,
                'message' => $student['error'],
                'data' => null,
                'errors' => null
            ], $student['code']);
        }

        $res = $this->service->getSchedulesByDay(
            $student['data']['classroom']->classroom_id,
            $day
        );

        if (!$res['success']) {
            return response()->json([
                'status' => false,
                'message' => $res['error'],
                'data' => null,
                'errors' => null
            ], 400);
        }

        return response()->json(
            $this->service->formatDailyScheduleResponse(
                $student['data']['student'],
                $student['data']['classroom'],
                $res['data']
            ), 200
        );
    }
}
