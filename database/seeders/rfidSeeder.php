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
        $students = Student::all();

        if ($students->isEmpty()) {
            return;
        }

        // Jumlah RFID yang ingin dibuat = setengah dari total student
        $targetCount = (int) floor($students->count() / 2);

        // Ambil student secara acak sebanyak targetCount
        $selectedStudents = $students->random($targetCount);

        foreach ($selectedStudents as $student) {
            Rfid::create([
                'id' => (string) Str::uuid(),
                'rfid' => $this->generateUniqueRfid(),
                'student_id' => $student->id,
                'status' => RfidStatusEnum::ACTIVE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
