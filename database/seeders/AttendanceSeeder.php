<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatusEnum;
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
        DB::table('attendances')->delete();
        DB::table('attendance_rfids')->delete();

        $students = Student::with(['classroomStudents' => function ($q) {
            $q->where('status', 'active')->with('classroom');
        }])->get();

        if ($students->isEmpty()) {
            $this->command->error("Seeder Error: Tidak ada student di database!");
            return;
        }

        $startDate = Carbon::now()->subDays(6);
        $endDate   = Carbon::now();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $currentDate = $date->format('Y-m-d');
            $dayName     = strtolower($date->locale('en')->dayName);

            $this->command->info("Seeding for Date: {$currentDate} ({$dayName})");

            foreach ($students as $student) {
                $rfidRecord = DB::table('rfids')->where('student_id', $student->id)->first();
                $classroomStudent = $student->classroomStudents->first();
                $classroom = $classroomStudent?->classroom;

                $rand = rand(1, 100);
                $scenario = match (true) {
                    $rand <= 70 => 'present',
                    $rand <= 80 => 'late',
                    $rand <= 90 => 'sick',
                    default     => 'alpha',
                };

                // =========================================================
                // INSERT KE attendance_rfids (hanya jika hadir / terlambat)
                // =========================================================
                if (in_array($scenario, ['present', 'late'])) {
                    $rfidStatus  = $scenario === 'late'
                        ? AttendanceStatusEnum::LATE->value
                        : AttendanceStatusEnum::PRESENT->value;
                    $checkinTime  = $scenario === 'late' ? '07:15:00' : '06:45:00';
                    $checkoutTime = '15:00:00';

                    try {
                        DB::table('attendance_rfids')->insert([
                            'id' => Str::uuid()->toString(),
                            'student_id' => $student->id,
                            'classroom_student_id' => $classroomStudent?->id,
                            'rfid_id' => $rfidRecord?->id,
                            'date' => $currentDate,
                            'checkin_time' => $checkinTime,
                            'checkout_time' => $checkoutTime,
                            'status' => $rfidStatus,
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                    } catch (\Exception $e) {
                        $this->command->error("RFID Insert Error for Student {$student->id}: " . $e->getMessage());
                    }
                }

                // =========================================================
                // INSERT KE attendances (cross-check per jam pelajaran)
                // =========================================================
                if (!$classroom) continue;

                $schedules = LessonSchedule::with('lessonHour')
                    ->where('classroom_id', $classroom->id)
                    ->where('day', $dayName)
                    ->get()
                    ->sortBy(fn($s) => $s->lessonHour?->order);

                foreach ($schedules as $schedule) {
                    $lessonOrder = $schedule->lessonHour?->order ?? 1;

                    $crossStatus = AttendanceStatusEnum::ALPHA->value;
                    $isLocked = 0;
                    $isFinal = 0;

                    if ($scenario === 'present' || $scenario === 'late') {
                        $crossStatus = AttendanceStatusEnum::PRESENT->value;
                        $isFinal = 1;
                    } elseif ($scenario === 'sick') {
                        $crossStatus = AttendanceStatusEnum::SICK->value;
                        $isLocked = 1;
                        $isFinal = 1;
                    } elseif ($scenario === 'alpha' && rand(1, 100) > 20) {
                        $isFinal = 1;
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
                            'status' => $crossStatus,
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
