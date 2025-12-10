<?php

namespace Database\Seeders;

use App\Enums\RfidStatusEnum;
use App\Models\Rfid;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RfidSeeder extends Seeder
{
    // Persentase siswa yang punya RFID - MUDAH DIUBAH!
    private const RFID_PERCENTAGE = 50; // 50%

    public function run(): void
    {
        $students = Student::all();

        if ($students->isEmpty()) {
            $this->command->error('Students not found');
            return;
        }

        $rfidCount = ceil(($students->count() * self::RFID_PERCENTAGE) / 100);
        $selectedStudents = $students->random(min($rfidCount, $students->count()));

        foreach ($selectedStudents as $student) {
            Rfid::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'id' => Str::uuid(),
                    'rfid' => $this->generateUniqueRfid(),
                    'status' => RfidStatusEnum::ACTIVE->value,
                ]
            );
        }

        $this->command->info("Created {$selectedStudents->count()} RFID cards");
    }

    private function generateUniqueRfid(): string
    {
        do {
            $rfidNumber = mt_rand(1000000000, 9999999999);
        } while (Rfid::where('rfid', $rfidNumber)->exists());

        return (string) $rfidNumber;
    }
}