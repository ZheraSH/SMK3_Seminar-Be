<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStudentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' =>'required',
            'email' =>'required|email',
            'nisn' => 'required|numeric',
            'gender' => 'required',
            'image' => 'nullable|mimes:png,jpeg,jpg',
            'religion_id' => 'required|exists:religions,id',
            'birth_date' => 'required|date',
            'birth_place' => 'required',
            'address' => 'required',
            'number_kk' => 'required|numeric|min:0',
            'number_akta' => 'required|numeric|min:1',
            'order_child' => 'required|numeric|min:1',
            'count_siblings' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Email tidak valid',
            'gender.required' => 'Jenis kelamin tidak boleh kosong',
            'nisn.required' => 'NISN tidak boleh kosong',
            'nisn.numeric' => 'NISN harus berupa angka',
            'religion_id.required' => 'Agama tidak boleh kosong',
            'religion_id.exists' => 'Agama tidak ditemukan',
            'birth_date.required' => 'Tanggal lahir tidak boleh kosong',
            'birth_date.date' => 'Tanggal lahir harus berupa tanggal',
            'birth_place.required' => 'Tempat lahir tidak boleh kosong',
            'address.required' => 'Alamat tidak boleh kosong',
            'number_kk.required' => 'Nomor KK tidak boleh kosong',
            'number_kk.numeric' => 'Nomor KK harus berupa angka',
            'number_akta.required' => 'Nomor akta tidak boleh kosong',
            'number_akta.numeric' => 'Nomor akta harus berupa angka',
            'order_child.required' => 'Anak ke- tidak boleh kosong',
            'order_child.numeric' => 'Anak ke- harus berupa angka',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422));
    }
}
