<?php

namespace Database\Seeders;

use App\Models\AttendanceRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttendanceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $attendanceRules = [
            [
                'day' => 'monday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'day' => 'tuesday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'day' => 'wednesday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'day' => 'thursday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'day' => 'friday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '10:35',
                'checkout_end' => '11:30',
                'is_holiday' => false,
            ],
            [
                'day' => 'saturday',
                'checkin_start' => null,
                'checkin_end' => null,
                'checkout_start' => null,
                'checkout_end' => null,
                'is_holiday' => true,
            ],
            [
                'day' => 'sunday',
                'checkin_start' => null,
                'checkin_end' => null,
                'checkout_start' => null,
                'checkout_end' => null,
                'is_holiday' => true,
            ],
        ];

        foreach ($attendanceRules as $rule) {
            AttendanceRule::firstOrCreate(
                ['day' => $rule['day']],
                array_merge(['id' => Str::uuid()], $rule)
            );
        }
    }
}