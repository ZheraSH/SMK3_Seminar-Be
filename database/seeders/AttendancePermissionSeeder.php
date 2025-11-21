<?php

namespace Database\Seeders;

use App\Models\AttendancePermission;
use App\Models\Student;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::limit(5)->get();

        $counselors = Employee::whereHas('user.roles', function ($q) {
            $q->where('name', 'counselor');
        })->get();

        if ($counselors->isEmpty()) {
            $this->command->warn('No counselors found. Seeder skipped.');
            return;
        }

        foreach ($students as $student) {

            $count = rand(1, 3);

            for ($i = 0; $i < $count; $i++) {

                $status = fake()->randomElement(['pending', 'approved', 'rejected']);
                $counselor = $counselors->random();

                $data = [
                    'type' => fake()->randomElement(['sick', 'permission', 'dispensation']),
                    'start_date' => Carbon::now()->subDays(rand(1, 10)),
                    'end_date' => Carbon::now()->subDays(rand(0, 9)),
                    'reason' => fake()->sentence(8),
                    'proof' => null,
                    'status' => $status,
                    'student_id' => $student->id,
                    'counselor_id' => $status !== 'pending' ? $counselor->id : null,
                    'verified_at' => $status !== 'pending' ? Carbon::now()->subDay() : null,
                ];

                AttendancePermission::create($data);
            }
        }
    }
}
