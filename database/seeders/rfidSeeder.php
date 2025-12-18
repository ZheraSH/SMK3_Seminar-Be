<?php

namespace Database\Seeders;

use App\Enums\RfidStatusEnum;
use App\Models\Rfid;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RfidSeeder extends Seeder
{
    private const RFID_PERCENTAGE = 50;

    public function run(): void
    {
        $studentsQuery = Student::query()
            ->whereDoesntHave('rfid');

        $totalStudents = $studentsQuery->count();

        if ($totalStudents === 0) {
            $this->command->warn('Tidak ada student tanpa RFID');
            return;
        }

        $rfidCount = (int) ceil($totalStudents * self::RFID_PERCENTAGE / 100);

        $students = $studentsQuery
            ->inRandomOrder()
            ->limit($rfidCount)
            ->get();

        foreach ($students as $student) {
            Rfid::create([
                'id'         => (string) Str::uuid(),
                'student_id' => $student->id,
                'rfid'       => $this->generateUniqueRfid(),
                'status'     => RfidStatusEnum::ACTIVE->value,
            ]);
        }
    }

    private function generateUniqueRfid(): string
    {
        do {
            $rfidNumber = 'RF' . random_int(100000000, 999999999);
        } while (
            Rfid::where('rfid', $rfidNumber)->exists()
        );

        return $rfidNumber;
    }
}