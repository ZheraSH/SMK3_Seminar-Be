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
        $bk = Employee::whereHas('user', function($q) {
            $q->where('role', 'counselor');
        })->first();

        foreach ($students as $student) {
            // Pending Permission
            AttendancePermission::create([
                'type' => 'sick',
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addDays(2),
                'reason' => 'Sakit demam tinggi, perlu istirahat di rumah',
                'proof' => null,
                'status' => 'pending',
                'student_id' => $student->id,
                'bk_id' => $bk?->id,
            ]);

            // Approved Permission
            AttendancePermission::create([
                'type' => 'permission',
                'start_date' => Carbon::now()->subDays(2),
                'end_date' => Carbon::now()->subDays(2),
                'reason' => 'Acara keluarga yang tidak bisa ditinggalkan',
                'proof' => null,
                'status' => 'approved',
                'student_id' => $student->id,
                'bk_id' => $bk?->id,
                'verified_by' => $bk?->id,
                'verification_notes' => 'Disetujui sesuai surat keterangan',
                'verified_at' => Carbon::now()->subDay(),
            ]);

            // Rejected Permission
            AttendancePermission::create([
                'type' => 'dispensation',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'reason' => 'Ingin liburan ke luar kota',
                'proof' => null,
                'status' => 'rejected',
                'student_id' => $student->id,
                'bk_id' => $bk?->id,
                'verified_by' => $bk?->id,
                'verification_notes' => 'Alasan tidak memenuhi syarat dispensasi',
                'verified_at' => Carbon::now()->subDays(6),
            ]);
        }
    }
}