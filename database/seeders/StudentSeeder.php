<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
    }
}
