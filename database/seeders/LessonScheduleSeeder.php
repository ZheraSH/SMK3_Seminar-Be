<?php

namespace Database\Seeders;

use App\Enums\DayEnum;
use App\Models\Classroom;
use App\Models\Employee;
use App\Models\LessonHour;
use App\Models\LessonSchedule;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonScheduleSeeder extends Seeder
{
    private const MAX_SUBJECTS_PER_TEACHER = 3;
    private const MAX_HOURS_PER_DAY = 6;
    private const FILL_RATE = 0.85;

    private $teacherStats = [];

    public function run(): void
    {
        $classrooms = Classroom::with('levelClass')->get();
        $subjects = Subject::all();
        
        $teachers = Employee::whereHas('user.roles', function($q) {
            $q->where('name', 'teacher');
        })->with('user')->get();

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        foreach ($teachers as $teacher) {
            $this->teacherStats[$teacher->id] = [
                'subjects' => [],
                'daily_hours' => [],
                'total_hours' => 0,
            ];
        }

        $schedulesCreated = 0;
        $attempts = 0;
        $maxAttempts = 10000;

        foreach ($classrooms as $classroom) {
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('day', $day)
                    ->where('is_lesson', true)
                    ->orderBy('start')
                    ->get();

                foreach ($lessonHours as $lessonHour) {
                    $attempts++;
                    if ($attempts > $maxAttempts) break 3;

                    if (rand(1, 100) > (self::FILL_RATE * 100)) {
                        continue;
                    }

                    $subject = $this->selectSubjectForClassroom($subjects, $classroom);
                    if (!$subject) continue;

                    $teacher = $this->findAvailableTeacher(
                        $teachers, 
                        $subject->id, 
                        $classroom->id, 
                        $day, 
                        $lessonHour->id
                    );

                    if (!$teacher) continue;

                    try {
                        LessonSchedule::firstOrCreate(
                            [
                                'classroom_id' => $classroom->id,
                                'day' => $day,
                                'lesson_hour_id' => $lessonHour->id,
                            ],
                            [
                                'id' => Str::uuid(),
                                'subject_id' => $subject->id,
                                'teacher_id' => $teacher->id,
                            ]
                        );

                        $this->updateTeacherStats($teacher->id, $subject->id, $day);
                        $schedulesCreated++;

                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }
    }

    private function selectSubjectForClassroom($subjects, $classroom)
    {
        $level = $classroom->levelClass->name;
        
        $priorityMap = [
            'X' => ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Pend. Pancasila'],
            'XI' => ['Produktif PPLG', 'Matematika', 'Bahasa Inggris', 'PKK'],
            'XII' => ['Produktif PPLG', 'Praktik PPLG', 'Matematika', 'Bahasa Inggris'],
        ];

        $priorities = $priorityMap[$level] ?? [];
        
        foreach ($priorities as $subjectName) {
            $subject = $subjects->firstWhere('name', $subjectName);
            if ($subject) return $subject;
        }
        
        return $subjects->random();
    }

    private function findAvailableTeacher($teachers, $subjectId, $classroomId, $day, $lessonHourId)
    {
        $availableTeachers = [];
        
        foreach ($teachers as $teacher) {
            $teacherId = $teacher->id;
            $stats = $this->teacherStats[$teacherId];
            
            $hasConflict = LessonSchedule::where('teacher_id', $teacherId)
                ->where('day', $day)
                ->where('lesson_hour_id', $lessonHourId)
                ->exists();
            
            if ($hasConflict) continue;
            
            $hasSameClass = LessonSchedule::where('teacher_id', $teacherId)
                ->where('classroom_id', $classroomId)
                ->where('day', $day)
                ->exists();
            
            if ($hasSameClass) continue;
            
            $subjectCount = count($stats['subjects']);
            if ($subjectCount >= self::MAX_SUBJECTS_PER_TEACHER) {
                if (!in_array($subjectId, $stats['subjects'])) continue;
            }
            
            $dailyHours = $stats['daily_hours'][$day] ?? 0;
            if ($dailyHours >= self::MAX_HOURS_PER_DAY) continue;
            
            $priority = 0;
            if (in_array($subjectId, $stats['subjects'])) $priority += 10;
            if ($subjectCount < 2) $priority += 5;
            if ($dailyHours < 3) $priority += 3;
            
            $availableTeachers[$teacherId] = [
                'teacher' => $teacher,
                'priority' => $priority,
            ];
        }
        
        if (empty($availableTeachers)) return null;
        
        uasort($availableTeachers, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
        
        return reset($availableTeachers)['teacher'];
    }

    private function updateTeacherStats($teacherId, $subjectId, $day)
    {
        if (!in_array($subjectId, $this->teacherStats[$teacherId]['subjects'])) {
            $this->teacherStats[$teacherId]['subjects'][] = $subjectId;
        }
        
        $this->teacherStats[$teacherId]['daily_hours'][$day] = 
            ($this->teacherStats[$teacherId]['daily_hours'][$day] ?? 0) + 1;
        
        $this->teacherStats[$teacherId]['total_hours']++;
    }
}