<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'NPSN' => '20527175',
            'phone_number' => '(0324) 322576',
            'image' => 'SMKN3.png',
            'pos_code' => '69316',
            'address' => 'Jl. Kabupaten 103 Pamekasan',
            'head_school' => 'HJ. Sri Indrawati, S.Pd., MM',
            'NIP' => '19660716 198903200',
            'website_school' => 'https://smkn3pamekasan.sch.id/',
            'accreditation' => 'A',
            'max_point' => 500,
        ];

        School::firstOrCreate(['NPSN' => $data['NPSN']], $data);
    }
}
 