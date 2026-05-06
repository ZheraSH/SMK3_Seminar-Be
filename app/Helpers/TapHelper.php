<?php

namespace App\Helpers;

use App\Enums\AttendanceStatusEnum;
use Carbon\Carbon;

class TapHelper
{
    public static function parseRuleTimeToCarbon(?string $time): ?Carbon
    {
        if (!$time) return null;
        $time = strlen($time) === 5 ? $time . ':00' : $time;
        try {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            return Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $time, 'Asia/Jakarta');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function calculateAttendanceStatus(Carbon $tapTime, Carbon $expected): string
    {
        $lateLimit = $expected->copy()->addMinutes(10);
        
        if ($tapTime->lte($expected)) {
            return AttendanceStatusEnum::PRESENT->value;
        } elseif ($tapTime->lte($lateLimit)) {
            return AttendanceStatusEnum::LATE->value;
        } else {
            return AttendanceStatusEnum::ALPHA->value;
        }
    }

    public static function getSafeEnumLabel($value, string $enumClass): string
    {
        if ($value instanceof $enumClass) return $value->label();
        try {
            return $enumClass::from($value)->label();
        } catch (\Throwable $e) {
            return 'Tidak Diketahui';
        }
    }

    public static function getSafeEnumValue($value, string $enumClass): string
    {
        if ($value instanceof $enumClass) {
            return $value->value;
        }
        return (string)$value;
    }

    // public static function nowWib(): Carbon
    // {
    //     return Carbon::now('Asia/Jakarta');
    // }

    // public static function getIndonesianTime(): string
    // {
    //     return self::nowWib()->format('H:i') . ' WIB';
    // }

    // public static function parseDateTimeToCarbon(?string $dateTime): ?Carbon
    // {
    //     if (!$dateTime) return null;
    //     try {
    //         return Carbon::parse($dateTime)->timezone('Asia/Jakarta');
    //     } catch (\Throwable $e) {
    //         return null;
    //     }
    // }

    // public static function isWithinTimeRange(Carbon $now, ?string $start, ?string $end): bool
    // {
    //     $s = self::parseRuleTimeToCarbon($start);
    //     $e = self::parseRuleTimeToCarbon($end);
    //     if (!$s || !$e) return false;
    //     return $now->between($s, $e);
    // }

    // public static function isDuplicateTap($attendance, Carbon $tapTime, string $type): bool
    // {
    //     if (!$attendance) return false;

    //     $existing = null;
    //     if ($type === 'checkin' && !empty($attendance->checkin_time)) {
    //         $existing = self::parseDateTimeToCarbon($attendance->checkin_time);
    //     } elseif ($type === 'checkout' && !empty($attendance->checkout_time)) {
    //         $existing = self::parseDateTimeToCarbon($attendance->checkout_time);
    //     }

    //     if (!$existing) return false;

    //     return $tapTime->diffInMinutes($existing) < 5;
    // }

    // public static function calculateMinutesLate(Carbon $tapTime, Carbon $expected): int
    // {
    //     if ($tapTime->lte($expected)) return 0;
    //     return $tapTime->diffInMinutes($expected);
    // }

    // public static function formatTimeForDisplay(?string $time): string
    // {
    //     if (!$time) return '-';
    //     try {
    //         return self::parseDateTimeToCarbon($time)?->format('H:i:s') ?? '-';
    //     } catch (\Throwable $e) {
    //         return '-';
    //     }
    // }

    // public static function formatDateTimeForDisplay(?string $datetime): string
    // {
    //     if (!$datetime) return '-';
    //     try {
    //         return self::parseDateTimeToCarbon($datetime)?->format('d/m/Y H:i') ?? '-';
    //     } catch (\Throwable $e) {
    //         return '-';
    //     }
    // }

}