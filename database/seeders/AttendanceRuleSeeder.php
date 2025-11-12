<?php
// database/seeders/AttendanceRuleSeeder.php
namespace Database\Seeders;

use App\Models\AttendanceRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttendanceRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attendanceRules = [
            [
                'id' => Str::uuid(),
                'day' => 'monday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'tuesday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'wednesday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'thursday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '15:20',
                'checkout_end' => '16:00',
                'is_holiday' => false,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'friday',
                'checkin_start' => '05:00',
                'checkin_end' => '07:00',
                'checkout_start' => '10:35',
                'checkout_end' => '11:30',
                'is_holiday' => false,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'saturday',
                'checkin_start' => null,
                'checkin_end' => null,
                'checkout_start' => null,
                'checkout_end' => null,
                'is_holiday' => true,
            ],
            [
                'id' => Str::uuid(),
                'day' => 'sunday',
                'checkin_start' => null,
                'checkin_end' => null,
                'checkout_start' => null,
                'checkout_end' => null,
                'is_holiday' => true,
            ],
        ];

        foreach ($attendanceRules as $rule) {
            AttendanceRule::create($rule);
        }
    }
}