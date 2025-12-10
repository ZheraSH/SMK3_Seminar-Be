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

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['name' => $subject],
                ['id' => Str::uuid()]
            );
        }
    }
}