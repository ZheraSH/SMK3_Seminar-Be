<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LessonHour;

class LessonHourSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Jam Ke 1', 'start' => '07:00', 'end' => '07:45'],
            ['name' => 'Jam Ke 2', 'start' => '07:45', 'end' => '08:30'],
            ['name' => 'Jam Ke 3', 'start' => '08:30', 'end' => '09:15'],
            ['name' => 'Istirahat', 'start' => '09:15', 'end' => '10:00'],
            ['name' => 'Jam Ke 4', 'start' => '10:00', 'end' => '10:45'],
            ['name' => 'Jam Ke 5', 'start' => '10:45', 'end' => '11:30'],
            ['name' => 'Jam Ke 6', 'start' => '11:30', 'end' => '12:15'],
        ];

        foreach ($data as $item) {
            LessonHour::create($item);
        }
    }
}