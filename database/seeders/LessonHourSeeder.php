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
        $defaultLessonHours = [
            ['name' => 'Jam Ke 1', 'start' => '06:45', 'end' => '07:50', 'is_lesson' => true],
            ['name' => 'Jam Ke 2', 'start' => '07:50', 'end' => '08:30', 'is_lesson' => true],
            ['name' => 'Jam Ke 3', 'start' => '08:30', 'end' => '09:10', 'is_lesson' => true],
            ['name' => 'Jam Ke 4', 'start' => '09:10', 'end' => '09:50', 'is_lesson' => true],
            ['name' => 'Istirahat', 'start' => '09:50', 'end' => '10:10', 'is_lesson' => false],
            ['name' => 'Jam Ke 5', 'start' => '10:10', 'end' => '10:50', 'is_lesson' => true],
            ['name' => 'Jam Ke 6', 'start' => '10:50', 'end' => '11:30', 'is_lesson' => true],
            ['name' => 'Jam Ke 7', 'start' => '11:30', 'end' => '12:10', 'is_lesson' => true],
            ['name' => 'Istirahat', 'start' => '12:10', 'end' => '13:00', 'is_lesson' => false],
            ['name' => 'Jam Ke 8', 'start' => '13:00', 'end' => '13:35', 'is_lesson' => true],
            ['name' => 'Jam Ke 9', 'start' => '13:35', 'end' => '14:10', 'is_lesson' => true],
            ['name' => 'Jam Ke 10', 'start' => '14:10', 'end' => '14:45', 'is_lesson' => true],
            ['name' => 'Jam Ke 11', 'start' => '14:45', 'end' => '15:20', 'is_lesson' => true],
        ];

        $fridayLessonHours = [
            ['name' => 'Jam Ke 1', 'start' => '07:30', 'end' => '08:05', 'is_lesson' => true],
            ['name' => 'Jam Ke 2', 'start' => '08:05', 'end' => '08:40', 'is_lesson' => true],
            ['name' => 'Jam Ke 3', 'start' => '08:40', 'end' => '09:15', 'is_lesson' => true],
            ['name' => 'Istirahat', 'start' => '09:15', 'end' => '09:35', 'is_lesson' => false],
            ['name' => 'Jam Ke 4', 'start' => '09:35', 'end' => '10:05', 'is_lesson' => true],
            ['name' => 'Jam Ke 5', 'start' => '10:05', 'end' => '10:35', 'is_lesson' => true],

        ];

        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
        ];

        $data = [];

        foreach ($days as $day) {
            foreach ($defaultLessonHours as $hour) {
                $data[] = $this->buildRow($day, $hour);
            }
        }
        foreach ($fridayLessonHours as $hour) {
            $data[] = $this->buildRow(DayEnum::FRIDAY->value, $hour);
        }
        LessonHour::insert($data);
    }

    private function buildRow(string $day, array $hour): array
    {
        return [
            'id' => (string) Str::uuid(),
            'day' => $day,
            'name' => $hour['name'],
            'start' => $hour['start'],
            'end' => $hour['end'],
            'is_lesson' => $hour['is_lesson'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
