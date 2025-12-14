<?php

namespace Database\Seeders;

use App\Enums\DayEnum;
use App\Models\LessonHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonHourSeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            DayEnum::MONDAY->value,
            DayEnum::TUESDAY->value,
            DayEnum::WEDNESDAY->value,
            DayEnum::THURSDAY->value,
            DayEnum::FRIDAY->value,
        ];

        foreach ($days as $day) {
            $this->seedDay($day);
        }
    }

    private function seedDay(string $day): void
    {

        if ($day === DayEnum::FRIDAY->value) {
            $hours = $this->getFridayHours();
        } else {
            $hours = $this->getRegularHours();
        }

        usort($hours, function ($a, $b) {
            return strcmp($a['start'], $b['start']);
        });

        $lessonCounter = 0;
        $breakCounter = 0;

        foreach ($hours as $hour) {
            $isLesson = $hour['is_lesson'];
            
            if ($isLesson) {
                $lessonCounter++;
                $order = $lessonCounter;
                $name = "Jam ke - {$lessonCounter}";
            } else {
                $breakCounter++;
                $order = $breakCounter;
                $name = "Istirahat - {$breakCounter}";
            }

            LessonHour::create([
                'id' => Str::uuid(),
                'day' => $day,
                'name' => $name,
                'start' => $hour['start'],
                'end' => $hour['end'],
                'is_lesson' => $isLesson,
                'order' => $order,
            ]);
        }
    }

    private function getRegularHours(): array
    {
        return [
            ['start' => '06:45', 'end' => '07:50', 'is_lesson' => true],
            ['start' => '07:50', 'end' => '08:30', 'is_lesson' => true],
            ['start' => '08:30', 'end' => '09:10', 'is_lesson' => true],
            ['start' => '09:10', 'end' => '09:50', 'is_lesson' => true],
            ['start' => '09:50', 'end' => '10:10', 'is_lesson' => false],
            ['start' => '10:10', 'end' => '10:50', 'is_lesson' => true],
            ['start' => '10:50', 'end' => '11:30', 'is_lesson' => true],
            ['start' => '11:30', 'end' => '12:10', 'is_lesson' => true],
            ['start' => '12:10', 'end' => '13:00', 'is_lesson' => false],
            ['start' => '13:00', 'end' => '13:35', 'is_lesson' => true],
            ['start' => '13:35', 'end' => '14:10', 'is_lesson' => true],
            ['start' => '14:10', 'end' => '14:45', 'is_lesson' => true],
            ['start' => '14:45', 'end' => '15:20', 'is_lesson' => true],
        ];
    }

    private function getFridayHours(): array
    {
        return [
            ['start' => '07:30', 'end' => '08:05', 'is_lesson' => true],
            ['start' => '08:05', 'end' => '08:40', 'is_lesson' => true],
            ['start' => '08:40', 'end' => '09:15', 'is_lesson' => true],
            ['start' => '09:15', 'end' => '09:35', 'is_lesson' => false],
            ['start' => '09:35', 'end' => '10:05', 'is_lesson' => true],
            ['start' => '10:05', 'end' => '10:35', 'is_lesson' => true],
        ];
    }
}