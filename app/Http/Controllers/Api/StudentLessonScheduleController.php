<?php

namespace App\Http\Controllers\Api;
  //
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class StudentLessonScheduleController extends Controller
{
    public function index(Student $student)
    {
        try {
            $classroom = DB::table('classroom_students')
                ->where('student_id', $student->id)
                ->first();

            if (!$classroom) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Student does not belong to any classroom'
                ], 404);
            }

            $schedules = DB::table('lesson_schedules')
                ->where('classroom_id', $classroom->classroom_id)
                ->get();

            $enrichedSchedules = $this->enrichSchedulesWithNames($schedules);

            $groupedSchedules = $this->groupSchedulesByDay($enrichedSchedules);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name ?? 'Unknown Student',
                    ],
                    'classroom' => [
                        'id' => $classroom->classroom_id,
                        'name' => $this->getClassName($classroom->classroom_id),
                    ],
                    'schedules' => $groupedSchedules
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Student Schedule Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve schedule',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    private function enrichSchedulesWithNames($schedules)
    {
        return $schedules->map(function ($schedule) {
            
            $subjectName = $this->getSubjectName($schedule->subject_id);
            
            $teacherName = $this->getTeacherName($schedule->employee_id);
            
            $lessonInfo = $this->getLessonHourInfo($schedule->lesson_hour_id);
            
            $className = $this->getClassName($schedule->classroom_id);

            return [
                'id' => $schedule->id,
                'day' => $this->formatDayName($schedule->day),
                'subject' => $subjectName,
                'teacher' => $teacherName,
                'start_time' => $this->formatTime($lessonInfo['start_time']),
                'end_time' => $this->formatTime($lessonInfo['end_time']),
                'session' => $lessonInfo['session'],
                'classroom' => $className
            ];
        });
    }

    private function getSubjectName($subjectId)
    {
        try {
            $subject = DB::table('subjects')->where('id', $subjectId)->first();
            return $subject ? $subject->name : 'Mata Pelajaran Tidak Ditemukan';
        } catch (\Exception $e) {
            return 'Mata Pelajaran Tidak Ditemukan';
        }
    }

    private function getTeacherName($employeeId)
    {
        try {
            $employee = DB::table('employees')->where('id', $employeeId)->first();
            
            if (!$employee) {
                return 'Guru Tidak Ditemukan';
            }

            $user = DB::table('users')->where('id', $employee->user_id)->first();
            
            if (!$user) {
                return 'Guru Tidak Ditemukan';
            }

            return $user->name ?? 'Guru Tidak Ditemukan';
            
        } catch (\Exception $e) {
            return 'Guru Tidak Ditemukan';
        }
    }

    private function getLessonHourInfo($lessonHourId)
    {
        try {
            $lessonHour = DB::table('lesson_hours')->where('id', $lessonHourId)->first();
            
            if (!$lessonHour) {
                return [
                    'start_time' => '08:00',
                    'end_time' => '09:30',
                    'session' => '1'
                ];
            }
            
            return [
                'start_time' => $lessonHour->start_time ?? $lessonHour->start ?? '08:00',
                'end_time' => $lessonHour->end_time ?? $lessonHour->end ?? '09:30',
                'session' => $lessonHour->session ?? $lessonHour->order ?? '1'
            ];
            
        } catch (\Exception $e) {
            return [
                'start_time' => '08:00',
                'end_time' => '09:30',
                'session' => '1'
            ];
        }
    }

    private function getClassName($classroomId)
    {
        try {
            $classroom = DB::table('classrooms')->where('id', $classroomId)->first();
            return $classroom ? $classroom->name : 'Kelas Tidak Ditemukan';
        } catch (\Exception $e) {
            return 'Kelas Tidak Ditemukan';
        }
    }

    private function formatDayName($day)
    {
        $dayMap = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa', 
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu'
        ];

        return $dayMap[strtolower($day)] ?? ucfirst($day);
    }

    private function formatTime($time)
    {
        if (!$time) return '08:00';
        
        if (strpos($time, ':') !== false) {
            return substr($time, 0, 5);
        }
        
        return $time;
    }

    private function groupSchedulesByDay($schedules)
    {
        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $grouped = [];
        
        foreach ($daysOrder as $day) {
            $daySchedules = $schedules->where('day', $day)->values();
            
            if ($daySchedules->isNotEmpty()) {
                $grouped[] = [
                    'day' => $day,
                    'schedules' => $daySchedules->sortBy('session')->values()
                ];
            }
        }
        
        return $grouped;
    }


}