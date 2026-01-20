<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png',
                'max:1048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Foto wajib diisi',
            'photo.image' => 'File harus berupa gambar',
            'photo.mimes' => 'Foto harus berformat jpeg, jpg, atau png',
            'photo.max' => 'Ukuran foto maksimal 1MB',
        ];
    }
}
