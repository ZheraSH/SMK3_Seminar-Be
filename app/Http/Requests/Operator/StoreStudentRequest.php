<?php

namespace App\Http\Requests\Operator;

use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\User;
use App\Enums\GenderEnum;
use App\Http\Requests\ApiRequest;

class StoreStudentRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email'),
            ],
            'image' => 'nullable|mimes:png,jpeg,jpg|max:1024',
            'nisn' => [
                'required',
                'numeric',
                'digits:10',
                Rule::unique(Student::class, 'nisn'),
            ],
            'religion_id' => 'required|exists:religions,id',
            'gender' => 'required|in:' . implode(',', GenderEnum::values()),
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'number_kk' => 'required|numeric|digits:16',
            'number_akta' => 'required|numeric|digits_between:10,20',
            'order_child' => 'nullable|integer|min:0',
            'count_siblings' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'image.mimes' => 'Foto harus berekstensi png, jpg, atau jpeg',
            'image.max' => 'Ukuran foto maksimal 1MB',
            'nisn.required' => 'NISN tidak boleh kosong',
            'nisn.numeric' => 'NISN harus berupa angka',
            'nisn.digits' => 'NISN harus 10 digit',
            'nisn.unique' => 'NISN sudah terdaftar',
            'religion_id.required' => 'Agama tidak boleh kosong',
            'religion_id.exists' => 'Agama tidak ditemukan',
            'gender.required' => 'Jenis kelamin tidak boleh kosong',
            'gender.in' => 'Jenis kelamin tidak valid',
            'birth_date.required' => 'Tanggal lahir tidak boleh kosong',
            'birth_date.date' => 'Tanggal lahir harus berupa tanggal',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini',
            'birth_place.required' => 'Tempat lahir tidak boleh kosong',
            'birth_place.max' => 'Tempat lahir maksimal 255 karakter',
            'address.required' => 'Alamat tidak boleh kosong',
            'address.max' => 'Alamat maksimal 500 karakter',
            'number_kk.required' => 'Nomor KK tidak boleh kosong',
            'number_kk.numeric' => 'Nomor KK harus berupa angka',
            'number_kk.digits' => 'Nomor KK harus 16 digit',
            'number_akta.required' => 'Nomor akta tidak boleh kosong',
            'number_akta.numeric' => 'Nomor akta harus berupa angka',
            'number_akta.digits_between' => 'Nomor akta harus antara 10-20 digit',
            'order_child.integer' => 'Anak ke- harus berupa angka',
            'order_child.min' => 'Anak ke- minimal 1',
            'count_siblings.integer' => 'Jumlah saudara harus berupa angka',
            'count_siblings.min' => 'Jumlah saudara minimal 0',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'image' => 'foto',
            'nisn' => 'NISN',
            'religion_id' => 'agama',
            'gender' => 'jenis kelamin',
            'birth_date' => 'tanggal lahir',
            'birth_place' => 'tempat lahir',
            'address' => 'alamat',
            'number_kk' => 'nomor KK',
            'number_akta' => 'nomor akta',
            'order_child' => 'anak ke-',
            'count_siblings' => 'jumlah saudara',
        ];
    }
}