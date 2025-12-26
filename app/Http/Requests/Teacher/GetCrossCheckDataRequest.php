<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\ApiRequest;

class GetCrossCheckDataRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => 'required|exists:classrooms,id',
            'date' => 'required|date|date_format:Y-m-d',
            'lesson_order' => 'required|integer',
            'search' => 'sometimes|string|max:100',
            'limit' => 'sometimes|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Kelas wajib dipilih',
            'classroom_id.exists' => 'Kelas tidak ditemukan',
            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'lesson_order.required' => 'Urutan jam pelajaran wajib diisi',
        ];
    }
}