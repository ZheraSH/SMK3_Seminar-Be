<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Pend. Agama Islam',
            'Bahasa Indonesia',
            'Bahasa Inggris',
            'Bahasa Madura',
            'Seni Budaya',
            'Matematika',
            'PJOK',
            'PPKN',
            'PKK',
            'Produktif PPLG',
            'Produktif DKV',
            'Produktif BDP',
            'Produktif PH',
            'Produktif KCS',
            'Produktif Kuliner',
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['name' => $subject],
                ['id' => Str::uuid()]
            );
        }
    }
}