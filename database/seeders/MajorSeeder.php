<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MajorSeeder extends Seeder
{
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
                [
                    'id' => Str::uuid(),
                    'code' => $major['code']
                ]
            );
        }
    }
}