<?php

namespace Database\Seeders;

use App\Enums\RfidStatusEnum;
use App\Models\Rfid;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RfidSeeder extends Seeder
{
    private const RFID_PERCENTAGE = 100;

    public function run(): void
    {
        $students = Student::query()
            ->whereDoesntHave('rfid')
            ->get();

        if ($students->count() === 0) {
            $this->command->warn('Tidak ada student tanpa RFID');
            return;
        }

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
            $rfidNumber = (string) random_int(1000000000, 9999999999);
        } while (
            Rfid::where('rfid', $rfidNumber)->exists()
        );

        return $rfidNumber;
    }
}
