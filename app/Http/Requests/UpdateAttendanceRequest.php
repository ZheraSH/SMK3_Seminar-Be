<?php

namespace App\Http\Requests;

class UpdateAttendanceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|exists:students,id',
            'classroom_student_id' => 'nullable|exists:classroom_students,id',
            'date' => 'sometimes|date',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i|after:checkin_time',
            'status' => 'sometimes|in:hadir_tepat_waktu,terlambat,tidak_hadir,izin,sakit',
            'tap_type' => 'nullable|in:checkin,checkout,class_checkin',
            'proof' => 'nullable|in:rfid,manual,class,online',
            'rfid_id' => 'nullable|exists:rfids,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.exists' => 'Siswa tidak valid',
            'date.date' => 'Format tanggal tidak valid',
            'checkout_time.after' => 'Waktu pulang harus setelah waktu masuk',
            'status.in' => 'Status kehadiran tidak valid',
        ];
    }
}