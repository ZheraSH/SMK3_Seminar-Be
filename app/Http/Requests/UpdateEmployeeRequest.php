<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Models\Employee;
use App\Models\User;

class UpdateEmployeeRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employeeId = $this->route('id') ?? $this->input('id');

        $employee = Employee::find($employeeId);
        $userId = $employee?->user_id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique(User::class, 'email')->ignore($userId),
            ],
            'image' => 'nullable|mimes:png,jpg,jpeg',
            'NIP' => 'required|string|max:18',
            'NIK' => 'nullable|string|max:16',
            'religion_id' => 'nullable|exists:religions,id',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'nullable|string|max:20',
            'active' => 'boolean',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'image.mimes' => 'Foto harus berekstensi png, jpg, atau jpeg',
            'NIP.required' => 'NIP tidak boleh kosong',
            'NIP.unique' => 'NIP sudah digunakan',
            'NIP.max' => 'NIP maksimal 18 karakter',
            'NIK.max' => 'NIK maksimal 16 karakter',
            'religion_id.exists' => 'Agama tidak ditemukan',
            'gender.required' => 'Jenis kelamin tidak boleh kosong',
            'gender.in' => 'Jenis kelamin harus laki-laki atau perempuan',
            'birth_date.required' => 'Tanggal lahir tidak boleh kosong',
            'birth_date.date' => 'Tanggal lahir harus valid',
            'birth_place.required' => 'Tempat lahir tidak boleh kosong',
            'birth_place.max' => 'Tempat lahir terlalu panjang',
            'address.required' => 'Alamat tidak boleh kosong',
            'phone_number.max' => 'Nomor telepon terlalu panjang',
        ];
    }
}
