<?php

namespace Database\Seeders;

use App\Models\AttendancePermission;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendancePermissionSeeder extends Seeder
{
    private const MIN_PERMISSIONS_PER_STUDENT = 1;
    private const MAX_PERMISSIONS_PER_STUDENT = 3;

    public function run(): void
    {
        $students = Student::all();
        $counselors = Employee::whereHas('user.roles', function($q) {
            $q->where('name', 'counselor');
        })->get();

        if ($students->isEmpty() || $counselors->isEmpty()) {
            $this->command->error('Students or Counselors not found');
            return;
        }

        $permissionsCreated = 0;

        foreach ($students as $student) {
            $permissionCount = rand(self::MIN_PERMISSIONS_PER_STUDENT, self::MAX_PERMISSIONS_PER_STUDENT);
            
            for ($i = 0; $i < $permissionCount; $i++) {
                $status = $this->randomStatus();
                $counselor = $counselors->random();
                
                AttendancePermission::create([
                    'id' => Str::uuid(),
                    'type' => $this->randomType(),
                    'start_date' => Carbon::now()->subDays(rand(1, 30)),
                    'end_date' => Carbon::now()->subDays(rand(0, 5)),
                    'reason' => $this->randomReason(),
                    'proof' => null,
                    'status' => $status,
                    'student_id' => $student->id,
                    'counselor_id' => $status !== 'pending' ? $counselor->id : null,
                    'verified_at' => $status !== 'pending' ? Carbon::now()->subDay() : null,
                ]);
                
                $permissionsCreated++;
            }
        }
    }

    private function randomStatus(): string
    {
        $statuses = ['pending', 'approved', 'rejected'];
        return $statuses[array_rand($statuses)];
    }

    private function randomType(): string
    {
        $types = ['sick', 'permission', 'dispensation'];
        return $types[array_rand($types)];
    }

    private function randomReason(): string
    {
        $reasons = [
            'Sakit demam',
            'Keluarga meninggal',
            'Kegiatan sekolah',
            'Izin keluarga',
            'Acara penting',
            'Kondisi kesehatan'
        ];
        return $reasons[array_rand($reasons)];
    }
}