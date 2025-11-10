<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.a
     */
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
            // 'Produktif DKV',
            // 'Praktik BDP',
            // 'Praktik PH',
            // 'Praktik KCS',
            // 'Praktik Kuliner',
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate([
                'name' => $subject,
            ]);
        }
    }
}
    