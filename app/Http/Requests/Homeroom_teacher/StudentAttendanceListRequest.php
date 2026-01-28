<?php

namespace App\Http\Requests\Homeroom_teacher;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Enums\AttendanceStatusEnum;

class StudentAttendanceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(AttendanceStatusEnum::values())],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date' => 'Format tanggal tidak valid',
            'date.date_format' => 'Format tanggal harus Y-m-d',
            'date.before_or_equal' => 'Tanggal tidak boleh melebihi hari ini',
            'search.string' => 'Pencarian harus berupa teks',
            'search.max' => 'Pencarian maksimal 255 karakter',
            'status.in' => 'Status harus salah satu dari: ' . implode(', ', AttendanceStatusEnum::values()),
            'per_page.integer' => 'Per page harus berupa angka',
            'per_page.min' => 'Per page minimal 1',
            'per_page.max' => 'Per page maksimal 100',
        ];
    }
}
