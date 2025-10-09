<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mapel;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapels = [
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Bahasa Madura',
            'Pend. Agama Islam',
            'Pend. Pancasila',
            'Matematika',
            'Seni Budaya',
            'PJOK',
            'PKK',
            'Produktif PPLG',
            'Produktif DKV',
            'Praktik BDP',
            'Praktik PH',
            'Praktik KCS',
            'Praktik Kuliner',
        ];

        foreach ($mapels as $mapel) {
            Mapel::firstOrCreate([
                'name' => $mapel,
            ]);
        }
    }
}
