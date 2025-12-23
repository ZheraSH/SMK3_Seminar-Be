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

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Required data missing!');
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

        $createdCount = 0;
        $teacherLoad = [];
        $teacherTimeSlots = [];

        foreach ($employees as $employee) {
            $teacherLoad[$employee->id] = 0;
            $teacherTimeSlots[$employee->id] = [];
        }

        foreach ($employees as $employee) {
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('is_lesson', true)
                    ->where('day', $day)
                    ->orderBy('start')
                    ->get();

                if ($lessonHours->isEmpty()) continue;

                $classroom = $classrooms->random();
                $availableHours = $lessonHours->filter(function ($hour) use ($day, $employee, $teacherTimeSlots) {
                    $timeSlotKey = $day . '_' . $hour->id;
                    return !in_array($timeSlotKey, $teacherTimeSlots[$employee->id]);
                });

                if ($availableHours->isNotEmpty()) {
                    $lessonHour = $availableHours->random();
                    $subject = $subjects->random();

                    try {
                        LessonSchedule::updateOrCreate(
                            [
                                'classroom_id' => $classroom->id,
                                'day' => $day,
                                'lesson_hour_id' => $lessonHour->id,
                            ],
                            [
                                'id' => (string) Str::uuid(),
                                'subject_id' => $subject->id,
                                'teacher_id' => $employee->id,
                            ]
                        );
                        $createdCount++;

                        $timeSlotKey = $day . '_' . $lessonHour->id;
                        $teacherLoad[$employee->id]++;
                        $teacherTimeSlots[$employee->id][] = $timeSlotKey;
                    } catch (\Exception $e) {
                        $this->command->error("Error: " . $e->getMessage());
                    }
                }
            }
        }

        foreach ($classrooms as $classroom) {
            $this->command->info("Processing classroom: {$classroom->name}");
            
            foreach ($days as $day) {
                $lessonHours = LessonHour::where('is_lesson', true)
                    ->where('day', $day)
                    ->orderBy('start')
                    ->get();

                if ($lessonHours->isEmpty()) continue;

                foreach ($lessonHours as $lessonHour) {

                    $existingSchedule = LessonSchedule::where([
                        'classroom_id' => $classroom->id,
                        'day' => $day,
                        'lesson_hour_id' => $lessonHour->id,
                    ])->exists();

                    if ($existingSchedule) continue;

                    $timeSlotKey = $day . '_' . $lessonHour->id;

                    $availableEmployees = $employees->filter(function ($employee) use ($timeSlotKey, $teacherTimeSlots) {
                        return !in_array($timeSlotKey, $teacherTimeSlots[$employee->id]);
                    });

                    if ($availableEmployees->isNotEmpty()) {
                        $selectedEmployee = $availableEmployees->random();
                    } else {
                        $selectedEmployee = $employees->random();
                    }

                    $selectedSubject = $subjects->random();

                    try {
                        LessonSchedule::create([
                            'id' => (string) Str::uuid(),
                            'classroom_id' => $classroom->id,
                            'day' => $day,
                            'lesson_hour_id' => $lessonHour->id,
                            'subject_id' => $selectedSubject->id,
                            'teacher_id' => $selectedEmployee->id,
                        ]);
                        $createdCount++;
                        
                        $teacherLoad[$selectedEmployee->id]++;
                        $teacherTimeSlots[$selectedEmployee->id][] = $timeSlotKey;
                    } catch (\Exception $e) {
                        $this->command->error("Error: " . $e->getMessage());
                    }
                }
            }
        }

        $this->reportTeacherDistribution($teacherLoad, $employees, $createdCount);
    }

    private function reportTeacherDistribution($teacherLoad, $employees, $createdCount): void
    {
        $this->command->info("\n=== Teacher Load Distribution ===");
        
        $loadDistribution = [0, 0, 0, 0, 0];
        $totalTeachingHours = 0;
        $teachersWithLoad = 0;
        
        foreach ($teacherLoad as $teacherId => $load) {
            $teacher = $employees->firstWhere('id', $teacherId);
            $teacherName = $teacher ? $teacher->user->name : 'Unknown';
            
            if ($load > 0) {
                $teachersWithLoad++;
                $totalTeachingHours += $load;
            }
            
            if ($load === 0) $loadDistribution[0]++;
            elseif ($load <= 5) $loadDistribution[1]++;
            elseif ($load <= 10) $loadDistribution[2]++;
            elseif ($load <= 15) $loadDistribution[3]++;
            else $loadDistribution[4]++;
            
            if ($load > 0) {
                $this->command->info("{$teacherName}: {$load} teaching hours");
            }
        }

        $averageLoad = $teachersWithLoad > 0 ? round($totalTeachingHours / $teachersWithLoad, 1) : 0;
    }
}