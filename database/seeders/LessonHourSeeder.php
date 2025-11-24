<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LessonHour;
use App\Enums\DayEnum;
use Illuminate\Support\Str;

class LessonHourSeeder extends Seeder
{
    public function run(): void
    {
        $lessonHours = [
            ['name' => 'Jam Ke 1', 'start' => '07:00', 'end' => '07:45', 'is_lesson' => true],
            ['name' => 'Jam Ke 2', 'start' => '07:45', 'end' => '08:30', 'is_lesson' => true],
            ['name' => 'Jam Ke 3', 'start' => '08:30', 'end' => '09:15', 'is_lesson' => true],
            ['name' => 'Istirahat', 'start' => '09:15', 'end' => '10:00', 'is_lesson' => false],
            ['name' => 'Jam Ke 4', 'start' => '10:00', 'end' => '10:45', 'is_lesson' => true],
            ['name' => 'Jam Ke 5', 'start' => '10:45', 'end' => '11:30', 'is_lesson' => true],
            ['name' => 'Jam Ke 6', 'start' => '11:30', 'end' => '12:15', 'is_lesson' => true],
        ];

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        $data = [];

        foreach ($days as $day) {
            foreach ($lessonHours as $lessonHour) {
                $data[] = [
                    'id' => (string) Str::uuid(),
                    'day' => $day,
                    'name' => $lessonHour['name'],
                    'start' => $lessonHour['start'],
                    'end' => $lessonHour['end'],
                    'is_lesson' => $lessonHour['is_lesson'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        LessonHour::insert($data);
    }
}