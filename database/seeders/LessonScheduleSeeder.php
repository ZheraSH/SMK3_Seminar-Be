<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LessonSchedule;
use App\Models\LessonHour;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Classroom;
use App\Enums\DayEnum;
use Illuminate\Support\Str;

class LessonScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all();
        $subjects = Subject::all();
        $employees = Employee::all();
        $lessonHours = LessonHour::all();

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $employees->isEmpty() || $lessonHours->isEmpty()) {
            $this->command->warn('LessonScheduleSeeder: Data dependencies missing (classrooms, subjects, employees, or lesson_hours).');
            return;
        }

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        foreach ($classrooms as $classroom) {
            foreach ($days as $day) {
                $usedHours = $lessonHours->shuffle()->take(rand(4, 6));

                foreach ($usedHours as $hour) {
                    LessonSchedule::updateOrCreate(
                        [
                            'classroom_id' => $classroom->id,
                            'day' => $day,
                            'lesson_hour_id' => $hour->id,
                        ],
                        [
                            'id' => (string) Str::uuid(),
                            'subject_id' => $subjects->random()->id,
                            'employee_id' => $employees->random()->id,
                        ]
                    );
                }
            }
        }
    }
}
