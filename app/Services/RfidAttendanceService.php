<?php

namespace App\Services;

use App\Enums\AttendanceEnum;
use App\Models\Attendance;
use App\Models\ClassroomStudent;
use App\Models\ModelHasRfid;
use App\Models\Student;
use App\Services\Contracts\RfidAttendanceServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RfidAttendanceService implements RfidAttendanceServiceInterface
{
    public function tap(string $rfid, ?string $timestamp = null): Attendance
    {
        $bound = ModelHasRfid::where('rfid', $rfid)->first();
        if (!$bound) {
            throw new ModelNotFoundException('RFID not registered');
        }

        if ($bound->model_type !== 'Student') {
            throw new ModelNotFoundException('RFID is not bound to a student');
        }

        $student = Student::findOrFail($bound->model_id);

        $classroomStudent = ClassroomStudent::where('id_student', $student->id)->latest()->first();
        if (!$classroomStudent) {
            throw new ModelNotFoundException('Student is not assigned to any classroom');
        }

        $now = $timestamp ? Carbon::parse($timestamp) : Carbon::now();

        $attendance = Attendance::create([
            'classroom_student_id' => $classroomStudent->id,
            'status' => AttendanceEnum::PRESENT->value,
            'point' => 0,
            'proof' => null,
        ]);

        return $attendance->load(['classroomStudent']);
    }
}


