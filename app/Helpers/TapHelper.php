<?php

namespace App\Helpers;

use App\Enums\AttendanceStatusEnum;
use App\Enums\TapTypeEnum;
use Carbon\Carbon;

class TapHelper
{
    /**
     * Check if tap is duplicate (within 5 minutes)
     */
    public static function isDuplicateTap($existingAttendance, Carbon $tapTime, string $type): bool
    {
        if (!$existingAttendance) {
            return false;
        }

        if ($type === TapTypeEnum::CHECKIN->value && $existingAttendance->checkin_time) {
            $existingTime = Carbon::parse($existingAttendance->checkin_time);
            $timeDifference = $tapTime->diffInMinutes($existingTime);
            return $timeDifference < 5;
        }

        if ($type === TapTypeEnum::CHECKOUT->value && $existingAttendance->checkout_time) {
            $existingTime = Carbon::parse($existingAttendance->checkout_time);
            $timeDifference = $tapTime->diffInMinutes($existingTime);
            return $timeDifference < 5;
        }

        return false;
    }

    /**
     * Calculate attendance status (present or late)
     */
    public static function calculateAttendanceStatus(Carbon $tapTime, Carbon $expectedTime): string
    {
        // Consider 5 minutes grace period
        $gracePeriod = $expectedTime->copy()->addMinutes(5);
        
        if ($tapTime->lte($gracePeriod)) {
            return AttendanceStatusEnum::PRESENT->value;
        }

        return AttendanceStatusEnum::LATE->value;
    }

    /**
     * Check if time is within specified range
     */
    public static function isWithinTimeRange(Carbon $time, ?string $startTime, ?string $endTime): bool
    {
        if (!$startTime || !$endTime) {
            return false;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return $time->between($start, $end);
    }

    /**
     * Format time for display
     */
    public static function formatTimeForDisplay(?string $time): string
    {
        if (!$time) return '-';
        
        return Carbon::parse($time)->format('H:i');
    }

    /**
     * Calculate minutes late
     */
    public static function calculateMinutesLate(Carbon $tapTime, Carbon $expectedTime): int
    {
        if ($tapTime->lte($expectedTime)) {
            return 0;
        }

        return $tapTime->diffInMinutes($expectedTime);
    }

    /**
     * Get current day in Indonesian format
     */
    public static function getIndonesianDay(): string
    {
        $days = [
            'sunday' => 'Minggu',
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
        ];

        $englishDay = strtolower(now()->englishDayOfWeek);
        return $days[$englishDay] ?? $englishDay;
    }
}