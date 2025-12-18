<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = DB::table('students')->get();

        if ($students->isEmpty()) {
            dd("Seeder Error: Tidak ada student di database!");
        }

        foreach ($students as $student) {

            for ($i = 0; $i < 10; $i++) {
                $date = Carbon::today()->subDays($i)->toDateString();

                $status = match (true) {
                    $i < 2 => AttendanceStatusEnum::LATE->value,
                    $i < 5 => AttendanceStatusEnum::ALPHA->value,
                    $i < 7 => AttendanceStatusEnum::LEAVE->value,
                    default => AttendanceStatusEnum::PRESENT->value
                };

                DB::table('attendances')->insert([
                    'id' => Str::uuid(),
                    'student_id' => $student->id,
                    'classroom_student_id' => null,
                    'rfid_id' => null,
                    'subject_id' => null,
                    'teacher_id' => null,
                    'lesson_schedule_id' => null,
                    'date' => $date,
                    'checkin_time' => '07:00:00',
                    'checkout_time' => '15:00:00',
                    'lesson_order' => 1,
                    'attendance_type' => 'rfid',
                    'status' => $status,
                    'proof' => 'manual',
                ]);
            }
        }
    }
}
