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
    private const MAX_SUBJECTS_PER_TEACHER = 5;
    private const MAX_HOURS_PER_DAY = 10;
    private const FILL_RATE = 1.0;
    private const MAX_SAME_SUBJECT_PER_DAY = 3;

    public function run(): void
    {
        $classrooms = Classroom::with('levelClass')->get();
        $subjects = Subject::all();

        $teachers = Employee::whereHas('user.roles', function ($q) {
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

        $teacherStats = [];
        foreach ($teachers as $teacher) {
            $teacherStats[$teacher->id] = [
                'subjects' => [],
                'daily_subjects' => [],
                'daily_hours' => [],
                'total_hours' => 0,
            ];
        }

        $schedulesCreated = 0;

        foreach ($classrooms as $classroom) {
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('day', $day)
                    ->orderBy('start')
                    ->get();

                foreach ($lessonHours as $lessonHour) {
                    if (!$lessonHour->is_lesson) {
                        try {
                            LessonSchedule::firstOrCreate(
                                [
                                    'classroom_id' => $classroom->id,
                                    'day' => $day,
                                    'lesson_hour_id' => $lessonHour->id,
                                ],
                                [
                                    'id' => Str::uuid(),
                                    'subject_id' => null,
                                    'teacher_id' => null,
                                ]
                            );
                        } catch (\Exception $e) {}
                        continue;
                    }

                    if (rand(1, 100) > (self::FILL_RATE * 100)) {
                        continue;
                    }

                    $subject = $this->selectSubjectForClassroom($subjects, $classroom);
                    if (!$subject) continue;

                    $teacher = $this->findAvailableTeacher(
                        $teachers,
                        $teacherStats,
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

                        $this->updateTeacherStats($teacherStats, $teacher->id, $subject->id, $day);
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

    private function findAvailableTeacher($teachers, &$teacherStats, $subjectId, $classroomId, $day, $lessonHourId)
    {
        $availableTeachers = [];

        foreach ($teachers as $teacher) {
            $teacherId = $teacher->id;
            $stats = $teacherStats[$teacherId];

            $hasTimeConflict = LessonSchedule::where('teacher_id', $teacherId)
                ->where('day', $day)
                ->where('lesson_hour_id', $lessonHourId)
                ->exists();

            if ($hasTimeConflict) {
                continue;
            }

            $hasClassConflict = LessonSchedule::where('teacher_id', $teacherId)
                ->where('classroom_id', $classroomId)
                ->where('day', $day)
                ->where('lesson_hour_id', $lessonHourId)
                ->exists();

            if ($hasClassConflict) {
                continue;
            }

            $differentSubjectCount = count($stats['subjects']);
            if ($differentSubjectCount >= self::MAX_SUBJECTS_PER_TEACHER) {
                if (!in_array($subjectId, $stats['subjects'])) {
                    continue;
                }
            }

            $dailyHours = $stats['daily_hours'][$day] ?? 0;
            if ($dailyHours >= self::MAX_HOURS_PER_DAY) {
                continue;
            }

            $sameSubjectTodayCount = 0;
            if (isset($stats['daily_subjects'][$day])) {
                foreach ($stats['daily_subjects'][$day] as $dailySubject) {
                    if ($dailySubject === $subjectId) {
                        $sameSubjectTodayCount++;
                    }
                }
            }

            if ($sameSubjectTodayCount >= self::MAX_SAME_SUBJECT_PER_DAY) {
                continue;
            }

            $priority = 0;

            if (in_array($subjectId, $stats['subjects'])) {
                $priority += 10;
            }

            if ($differentSubjectCount < 2) {
                $priority += 5;
            }

            if ($dailyHours < 3) {
                $priority += 3;
            }

            if ($sameSubjectTodayCount === 1) {
                $priority += 2;
            }

            $availableTeachers[$teacherId] = [
                'teacher' => $teacher,
                'priority' => $priority,
                'subject_count' => $differentSubjectCount,
                'daily_hours' => $dailyHours,
                'same_subject_today' => $sameSubjectTodayCount,
            ];
        }

        if (empty($availableTeachers)) {
            return null;
        }

        uasort($availableTeachers, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return $a['daily_hours'] <=> $b['daily_hours'];
            }
            return $b['priority'] <=> $a['priority'];
        });

        return reset($availableTeachers)['teacher'];
    }

    private function updateTeacherStats(&$teacherStats, $teacherId, $subjectId, $day)
    {
        if (!in_array($subjectId, $teacherStats[$teacherId]['subjects'])) {
            $teacherStats[$teacherId]['subjects'][] = $subjectId;
        }

        if (!isset($teacherStats[$teacherId]['daily_subjects'][$day])) {
            $teacherStats[$teacherId]['daily_subjects'][$day] = [];
        }
        $teacherStats[$teacherId]['daily_subjects'][$day][] = $subjectId;

        $teacherStats[$teacherId]['daily_hours'][$day] =
            ($teacherStats[$teacherId]['daily_hours'][$day] ?? 0) + 1;

        $teacherStats[$teacherId]['total_hours']++;
    }
}
