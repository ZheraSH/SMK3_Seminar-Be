<?php

namespace App\Helpers;

use App\Enums\AttendanceStatusEnum;
use Carbon\Carbon;

class TapHelper
{
    // parse rule time like "05:00" or "05:00:00" into Carbon with today's date in Asia/Jakarta
    public static function parseRuleTimeToCarbon(?string $time): ?Carbon
    {
        if (!$time) return null;
        // Normalize to H:i:s
        $time = strlen($time) === 5 ? $time . ':00' : $time;
        try {
            $today = Carbon::now('Asia/Jakarta')->toDateString();
            return Carbon::createFromFormat('Y-m-d H:i:s', $today . ' ' . $time, 'Asia/Jakarta');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // parse any datetime string to Carbon Asia/Jakarta (safe)
    public static function parseDateTimeToCarbon(?string $dateTime): ?Carbon
    {
        if (!$dateTime) return null;
        try {
            return Carbon::parse($dateTime)->timezone('Asia/Jakarta');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function nowWib(): Carbon
    {
        return Carbon::now('Asia/Jakarta');
    }

    // Check if now within rule start/end (both are rule strings)
    public static function isWithinTimeRange(Carbon $now, ?string $start, ?string $end): bool
    {
        $s = self::parseRuleTimeToCarbon($start);
        $e = self::parseRuleTimeToCarbon($end);
        if (!$s || !$e) return false;
        return $now->between($s, $e);
    }

    // Duplicate tap detection (within 5 minutes)
    public static function isDuplicateTap($attendance, Carbon $tapTime, string $type): bool
    {
        if (!$attendance) return false;

        $existing = null;
        if ($type === 'checkin' && !empty($attendance->checkin_time)) {
            $existing = self::parseDateTimeToCarbon($attendance->checkin_time);
        } elseif ($type === 'checkout' && !empty($attendance->checkout_time)) {
            $existing = self::parseDateTimeToCarbon($attendance->checkout_time);
        }

        if (!$existing) return false;

        return $tapTime->diffInMinutes($existing) < 5;
    }

    public static function calculateAttendanceStatus(Carbon $tapTime, Carbon $expected): string
    {
        $grace = $expected->copy()->addMinutes(5);
        return $tapTime->lte($grace) ? AttendanceStatusEnum::PRESENT->value : AttendanceStatusEnum::LATE->value;
    }

    public static function calculateMinutesLate(Carbon $tapTime, Carbon $expected): int
    {
        if ($tapTime->lte($expected)) return 0;
        return $tapTime->diffInMinutes($expected);
    }

    // Format helpers for API output
    public static function formatTimeForDisplay(?string $time): string
    {
        if (!$time) return '-';
        try {
            // If stored as 'H:i:s' or ISO, parse and return H:i:s
            return self::parseDateTimeToCarbon($time)?->format('H:i:s') ?? '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    public static function formatDateTimeForDisplay(?string $datetime): string
    {
        if (!$datetime) return '-';
        try {
            return self::parseDateTimeToCarbon($datetime)?->format('d/m/Y H:i') ?? '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    public static function getIndonesianTime(): string
    {
        return self::nowWib()->format('H:i') . ' WIB';
    }

    // Safe enum getters (value or label)
    public static function getSafeEnumValue($value, string $enumClass): string
    {
        if ($value instanceof $enumClass) {
            return $value->value;
        }
        return (string)$value;
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
}

