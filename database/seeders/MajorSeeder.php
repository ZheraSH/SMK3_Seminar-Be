<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $majors = [
            ['name' => 'Pengembangan Perangkat Lunak & Game', 'code' => 'PPLG'],
            ['name' => 'Desain Komunikasi Visual', 'code' => 'DKV'],
            ['name' => 'Desain & Produksi Busana', 'code' => 'DPB'],
            ['name' => 'Kecantikan & Spa', 'code' => 'KCS'],
            ['name' => 'Perhotelan', 'code' => 'PH'],
            ['name' => 'Kuliner', 'code' => 'Kuliner'],
        ];

        foreach ($majors as $major) {
            Major::firstOrCreate(
                ['name' => $major['name']],
                ['code' => $major['code']]
            );
        }
    }
}
