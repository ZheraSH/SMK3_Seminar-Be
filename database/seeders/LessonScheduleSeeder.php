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

        if ($classrooms->isEmpty()) {
            $this->command->error('No classrooms found! Please run ClassroomSeeder first.');
            return;
        }
        if ($subjects->isEmpty()) {
            $this->command->error('No subjects found! Please run SubjectSeeder first.');
            return;
        }
        if ($employees->isEmpty()) {
            $this->command->error('No employees found! Please run EmployeeSeeder first.');
            return;
        }
        $this->command->info("Found {$classrooms->count()} classrooms, {$subjects->count()} subjects, {$employees->count()} employees");

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        $usedTeachers = [];
        $usedClassrooms = [];
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($classrooms as $classroom) {
            $this->command->info("Processing classroom: {$classroom->name}");
            
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('is_lesson', true)
                    ->where('day', $day)
                    ->orderBy('start')
                    ->get();

                if ($lessonHours->isEmpty()) {
                    $this->command->warn("No lesson hours found for day: {$day}");
                    continue;
                }

                foreach ($lessonHours as $hour) {
                    if (fake()->boolean(25)) {
                        $skippedCount++;
                        continue;
                    }

                    $timeSlotKey = $day . '_' . $hour->id;

                    if (isset($usedClassrooms[$classroom->id]) && in_array($timeSlotKey, $usedClassrooms[$classroom->id])) {
                        $skippedCount++;
                        continue;
                    }

                    $availableEmployees = $employees->filter(function ($employee) use ($timeSlotKey, $usedTeachers) {
                        return !isset($usedTeachers[$employee->id]) || !in_array($timeSlotKey, $usedTeachers[$employee->id]);
                    });

                    if ($availableEmployees->isEmpty()) {
                        $this->command->warn("No available teachers for {$day} at {$hour->start}");
                        $skippedCount++;
                        continue;
                    }

                    $selectedEmployee = $availableEmployees->random();
                    $selectedSubject = $subjects->random();

                    if (!isset($usedTeachers[$selectedEmployee->id])) {
                        $usedTeachers[$selectedEmployee->id] = [];
                    }
                    $usedTeachers[$selectedEmployee->id][] = $timeSlotKey;

                    if (!isset($usedClassrooms[$classroom->id])) {
                        $usedClassrooms[$classroom->id] = [];
                    }
                    $usedClassrooms[$classroom->id][] = $timeSlotKey;

                    try {
                        LessonSchedule::updateOrCreate(
                            [
                                'classroom_id' => $classroom->id,
                                'day' => $day,
                                'lesson_hour_id' => $hour->id,
                            ],
                            [
                                'id' => (string) Str::uuid(),
                                'subject_id' => $selectedSubject->id,
                                'employee_id' => $selectedEmployee->id,
                            ]
                        );
                        $createdCount++;
                    } catch (\Exception $e) {
                        $this->command->error("Error creating schedule: " . $e->getMessage());
                    }
                }
            }
        }
    }
}