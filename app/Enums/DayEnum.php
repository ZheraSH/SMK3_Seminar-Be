<?php

namespace App\Enums;

enum DayEnum: string
{
    case MONDAY = "monday";
    case TUESDAY = "tuesday";
    case WEDNESDAY = "wednesday";
    case THURSDAY = "thursday";
    case FRIDAY = "friday";
    case SATURDAY = "saturday";
    case SUNDAY = "sunday";

    public function label(): string
    {
        return match ($this) {
            self::MONDAY => "senin",
            self::TUESDAY => "selasa",
            self::WEDNESDAY => "rabu",
            self::THURSDAY => "kamis",
            self::FRIDAY => "jumat",
            self::SATURDAY => "sabtu",
            self::SUNDAY => "minggu",
        };
    }

    public static function translate(string $dayInIndonesian): string
    {
        return match($dayInIndonesian) {
            'senin' => self::MONDAY->value,
            'selasa' => self::TUESDAY->value,
            'rabu' => self::WEDNESDAY->value,
            'kamis' => self::THURSDAY->value,
            'jumat' => self::FRIDAY->value,
            'sabtu' => self::SATURDAY->value,
            'minggu' => self::SUNDAY->value,
            default => $dayInIndonesian,
        };
    }
}
