<?php

namespace App\Http\Requests;

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
            'date' => 'required|date',
            'lesson_order' => 'required|integer|min:2',
            'page' => 'sometimes|integer|min:1',
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
            'lesson_order.min' => 'Cross-check hanya untuk jam pelajaran ke-2 dan seterusnya',
        ];
    }
}