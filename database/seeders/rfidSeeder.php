<?php

namespace Database\Seeders;

use App\Models\Rfid;
use App\Models\Student;
use App\Enums\RfidStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RfidSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('user')->get();

        if ($students->isEmpty()) {
            return;
        }

        $totalStudents = $students->count();
        $assignedCount = (int) ceil($totalStudents * 0.6);
        $availableRfidCount = 10;

        // 1. Assign RFID ke sebagian siswa (ACTIVE)
        $assigned = 0;
        foreach ($students->take($assignedCount) as $student) {
            Rfid::create([
                'id' => (string) Str::uuid(),
                'rfid' => $this->generateUniqueRfid(),
                'student_id' => $student->id,
                'status' => RfidStatusEnum::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $assigned++;
        }

        // 2. Buat RFID available tanpa student (INACTIVE)
        $available = 0;
        for ($i = 0; $i < $availableRfidCount; $i++) {
            Rfid::create([
                'id' => (string) Str::uuid(),
                'rfid' => $this->generateUniqueRfid(),
                'student_id' => null,
                'status' => RfidStatusEnum::INACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $available++;
        }
    }

    private function generateUniqueRfid(): string
    {
        do {
            $rfidNumber = sprintf('%010d', mt_rand(1000000000, 9999999999));
        } while (Rfid::where('rfid', $rfidNumber)->exists());

        return $rfidNumber;
    }
}