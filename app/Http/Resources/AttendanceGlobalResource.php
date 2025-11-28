<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceGlobalResource extends JsonResource
{
    public function toArray($request)
    {
        $classroom = $this->student->classroomStudents->last()?->classroom;

        return [
            'nama_siswa' => $this->student->user->name,
            'kelas'      => $classroom?->name,
            'major_code' => $classroom?->major?->code,
            'tanggal'    => $this->date->toDateString(),
            'status'     => $this->status->value,
            'jam_masuk'  => $this->checkin_time,
            'jam_pulang' => $this->checkout_time,
        ];
    }
}
