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
    public function run(): void
    {
        $classrooms = Classroom::all();
        $subjects = Subject::all();
        $employees = Employee::whereHas('user.roles', function($q) {
            $q->where('name', 'teacher');
        })->get();

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Required data not found for LessonScheduleSeeder');
            return;
        }

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        $schedulesCreated = 0;

        foreach ($classrooms as $classroom) {
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('day', $day)
                    ->where('is_lesson', true)
                    ->orderBy('start')
                    ->get();

                foreach ($lessonHours as $lessonHour) {
                    // Skip randomly to create realistic schedule (not all hours filled)
                    if (rand(0, 10) > 7) continue;

                    $subject = $subjects->random();
                    $teacher = $employees->random();

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

                    $schedulesCreated++;
                }
            }
        }

        $this->command->info("Created {$schedulesCreated} lesson schedules");
    }
}