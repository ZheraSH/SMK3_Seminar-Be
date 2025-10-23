<?php

namespace App\Http\Requests;

use App\Models\Student;
class UpdateStudentRequest extends ApiRequest
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
        $studentId = $this->route('id') ?? $this->input('id');

        $student = Student::find($studentId);
        $userId = $student?->user_id;

        return [
            'name' => 'required',
            'email' => 'required|email',
            'image' => 'nullable|mimes:png,jpeg,jpg',
            'nisn' => 'required|numeric',
            'religion_id' => 'required|exists:religions,id',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'birth_place' => 'required',
            'address' => 'required',
            'number_kk' => 'required|numeric|min:0',
            'number_akta' => 'required|numeric|min:0',
            'order_child' => 'required|numeric|min:1',
            'count_siblings' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tidak boleh kosong',
            'image.mimes' => 'Foto harus berekstensi png, jpg dan jpeg',
            'religion_id.exists' => 'Agama tidak ditemukan',
            'gender.required' => 'Jenis kelamin tidak boleh kosong',
            'gender.in' => 'Jenis kelamin harus laki-laki atau perempuan',
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
            'count_siblings.numeric' => 'Jumlah saudara harus berupa angka',
        ];
    }
}
