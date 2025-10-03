<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Enums\GenderEnum;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Student::create([
            'id' => '1',
            'user_id' => '1',
            'nisn' => '1234567890',
            'nik' => '12345678910123',
            'religion_id' => '1',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => now(),
            'birth_place' => 'Sumenep',
            'address' => 'Pamekasan',
            'number_kk' => '123456789101234',
            'number_akta' => '1234567891012',
            'order_child' => '5',
            'count_siblings' => '5'
        ]);

        Student::create([
            'id' => '2',
            'user_id' => '2',
            'nisn' => '2234567890',
            'nik' => '22345678910123',
            'religion_id' => '2',
            'gender' => GenderEnum::FEMALE->value,
            'birth_date' => '2007-05-12',
            'birth_place' => 'Pamekasan',
            'address' => 'Jl. Raya Galis',
            'number_kk' => '223456789101234',
            'number_akta' => '2234567891012',
            'order_child' => '2',
            'count_siblings' => '3'
        ]);

        Student::create([
            'id' => '3',
            'user_id' => '3',
            'nisn' => '3234567890',
            'nik' => '32345678910123',
            'religion_id' => '1',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '2006-09-21',
            'birth_place' => 'Bangkalan',
            'address' => 'Jl. Trunojoyo',
            'number_kk' => '323456789101234',
            'number_akta' => '3234567891012',
            'order_child' => '1',
            'count_siblings' => '1'
        ]);

        Student::create([
            'id' => '4',
            'user_id' => '4',
            'nisn' => '4234567890',
            'nik' => '42345678910123',
            'religion_id' => '3',
            'gender' => GenderEnum::FEMALE->value,
            'birth_date' => '2008-02-10',
            'birth_place' => 'Sampang',
            'address' => 'Jl. KH Hasyim Asyari',
            'number_kk' => '423456789101234',
            'number_akta' => '4234567891012',
            'order_child' => '3',
            'count_siblings' => '4'
        ]);

        Student::create([
            'id' => '5',
            'user_id' => '5',
            'nisn' => '5234567890',
            'nik' => '52345678910123',
            'religion_id' => '1',
            'gender' => GenderEnum::MALE->value,
            'birth_date' => '2007-07-07',
            'birth_place' => 'Sumenep',
            'address' => 'Jl. Merdeka',
            'number_kk' => '523456789101234',
            'number_akta' => '5234567891012',
            'order_child' => '4',
            'count_siblings' => '6'
        ]);

        Student::create([
            'id' => '6',
            'user_id' => '6',
            'nisn' => '6234567890',
            'nik' => '62345678910123',
            'religion_id' => '2',
            'gender' => GenderEnum::FEMALE->value,
            'birth_date' => '2006-11-15',
            'birth_place' => 'Probolinggo',
            'address' => 'Jl. Diponegoro',
            'number_kk' => '623456789101234',
            'number_akta' => '6234567891012',
            'order_child' => '2',
            'count_siblings' => '2'
        ]);
    }
}
