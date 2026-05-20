<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Enums\SchoolTypeEnum;
use App\Enums\AccreditationEnum;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schools')->insert([
            'id' => Str::uuid(),
            'logo' => 'logo/SMKN_3_PAMEKASAN_LOGO.svg',
            'name' => 'SMK NEGERI 3 PAMEKASAN',
            'principal_name' => 'Hj. Sri Indrawati S.Pd M.M',
            'npsn' => '20527275',
            'phone' => '(0324) 322576',
            'email' => 'smkn3pmk@yahoo.com',
            'school_type' => SchoolTypeEnum::SMK->value,
            'accreditation' => AccreditationEnum::A->value,
            'address' => 'Jl. Kabupaten No.103, Bugih, Pamekasan, Jawa Timur 69317',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
