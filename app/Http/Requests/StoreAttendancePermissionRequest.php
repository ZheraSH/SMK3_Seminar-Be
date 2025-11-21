<?php

namespace App\Http\Requests;

use App\Enums\PermissionTypeEnum;
use Illuminate\Validation\Rules\Enum;

class StoreAttendancePermissionRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(PermissionTypeEnum::class)],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis izin wajib dipilih',
            'type.enum' => 'Jenis izin tidak valid',
            'start_date.required' => 'Tanggal mulai wajib diisi',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'end_date.required' => 'Tanggal selesai wajib diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai',
            'reason.required' => 'Alasan wajib diisi',
            'reason.min' => 'Alasan minimal 10 karakter',
            'reason.max' => 'Alasan maksimal 500 karakter',
            'proof.mimes' => 'Format file harus jpg, jpeg, png, atau pdf',
            'proof.max' => 'Ukuran file maksimal 2MB',
        ];
    }
}