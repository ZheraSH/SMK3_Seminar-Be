<?php

namespace Database\Seeders;

use App\Enums\AttendanceProofEnum;
use App\Enums\AttendanceStatusEnum;
use App\Models\ClassroomStudents;
use App\Models\LessonSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clear existing data
        DB::table('attendances')->delete();

        $students = Student::with(['classroomStudents' => function ($q) {
            $q->where('status', 'active')->with('classroom');
        }])->get();

        if ($students->isEmpty()) {
            $this->command->error("Seeder Error: Tidak ada student di database!");
            return;
        }

        // 2. Define simulation range
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        // 3. Iterate Days
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $currentDate = $date->format('Y-m-d');
            $dayName = strtolower($date->locale('en')->dayName);

            $this->command->info("Seeding for Date: {$currentDate} ({$dayName})");

            foreach ($students as $student) {
                $rfidRecord = DB::table('rfids')->where('student_id', $student->id)->first();

                // Determine student behavior
                $rand = rand(1, 100);
                $scenario = match (true) {
                    $rand <= 70 => 'present',
                    $rand <= 80 => 'late',
                    $rand <= 90 => 'sick',
                    default => 'alpha'
                };

                $status = match ($scenario) {
                    'present' => AttendanceStatusEnum::PRESENT->value,
                    'late' => AttendanceStatusEnum::LATE->value,
                    'sick' => AttendanceStatusEnum::SICK->value,
                    default => AttendanceStatusEnum::ALPHA->value
                };

                $checkIn = null;
                $checkOut = null;
                $proof = AttendanceProofEnum::RFID->value;

                if (in_array($scenario, ['present', 'late'])) {
                    $checkIn = $scenario === 'late' ? '07:15:00' : '06:45:00';
                    $checkOut = '15:00:00';
                } elseif ($scenario === 'sick') {
                    $proof = AttendanceProofEnum::PERMISSION->value;
                }

                try {
                    DB::table('attendances')->insert([
                        'id' => Str::uuid()->toString(),
                        'student_id' => $student->id,
                        'classroom_student_id' => null,
                        'rfid_id' => $rfidRecord?->id,
                        'date' => $currentDate,
                        'checkin_time' => $checkIn,
                        'checkout_time' => $checkOut,
                        'lesson_order' => 1,
                        'attendance_type' => 'rfid',
                        'status' => $status,
                        'proof' => $proof,
                        'is_locked' => $scenario === 'sick' ? 1 : 0,
                        'is_final' => 1,
                        'overridden_by_permission_id' => null,
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    $this->command->error("RFID/Daily Status Insert Error for Student {$student->id}: " . $e->getMessage());
                }

                $classroomStudent = $student->classroomStudents->first();
                $classroom = $classroomStudent?->classroom;

                if (!$classroom) continue;

                $schedules = LessonSchedule::with('lessonHour')
                    ->where('classroom_id', $classroom->id)
                    ->where('day', $dayName)
                    ->get()
                    ->sortBy(function ($schedule) {
                        return $schedule->lessonHour?->order;
                    });

                foreach ($schedules as $schedule) {
                    $lessonOrder = $schedule->lessonHour?->order ?? 1;

                    $status = AttendanceStatusEnum::ALPHA->value;
                    $isLocked = 0;
                    $isFinal = 0;
                    $proof = AttendanceProofEnum::MANUAL->value;

                    if ($scenario === 'present') {
                        $status = AttendanceStatusEnum::PRESENT->value;
                        $isFinal = 1;
                        $proof = AttendanceProofEnum::CLASSROOM->value;
                    } elseif ($scenario === 'late') {
                        $status = ($lessonOrder == 1) ? AttendanceStatusEnum::LATE->value : AttendanceStatusEnum::PRESENT->value;
                        $isFinal = 1;
                        $proof = AttendanceProofEnum::CLASSROOM->value;
                    } elseif ($scenario === 'sick') {
                        $status = AttendanceStatusEnum::SICK->value;
                        $isLocked = 1;
                        $isFinal = 1;
                        $proof = AttendanceProofEnum::PERMISSION->value;
                    }
                    if ($scenario === 'alpha' && rand(1, 100) > 20) {
                        $isFinal = 1;
                        $proof = AttendanceProofEnum::CLASSROOM->value;
                    }

                    try {
                        DB::table('attendances')->insert([
                            'id' => Str::uuid()->toString(),
                            'student_id' => $student->id,
                            'classroom_student_id' => $classroomStudent?->id,
                            'lesson_schedule_id' => $schedule->id,
                            'subject_id' => $schedule->subject_id,
                            'teacher_id' => $schedule->teacher_id,
                            'date' => $currentDate,
                            'lesson_order' => $lessonOrder,
                            'attendance_type' => 'cross_check',
                            'status' => $status,
                            'proof' => $proof,
                            'is_locked' => $isLocked,
                            'is_final' => $isFinal,
                            'overridden_by_permission_id' => null,
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                    } catch (\Exception $e) {
                        $this->command->error("CrossCheck Insert Error for Student {$student->id}, Schedule {$schedule->id}: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
