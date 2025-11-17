<?php

namespace App\Http\Requests;

class StoreAttendanceRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'classroom_student_id' => 'nullable|exists:classroom_students,id',
            'date' => 'required|date',
            'checkin_time' => 'nullable|date_format:H:i',
            'checkout_time' => 'nullable|date_format:H:i|after:checkin_time',
            'status' => 'required|in:hadir_tepat_waktu,terlambat,tidak_hadir,izin,sakit',
            'tap_type' => 'nullable|in:checkin,checkout,class_checkin',
            'proof' => 'nullable|in:rfid,manual,class,online',
            'rfid_id' => 'nullable|exists:rfids,id',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa wajib dipilih',
            'student_id.exists' => 'Siswa tidak valid',
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'checkout_time.after' => 'Waktu pulang harus setelah waktu masuk',
            'status.required' => 'Status kehadiran wajib diisi',
            'status.in' => 'Status kehadiran tidak valid',
        ];
    }
}