<?php

namespace App\Enums;

enum DayEnum: string
{
    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';

    public function label(): string
    {
        return match ($this) {
            self::MONDAY => 'Senin',
            self::TUESDAY => 'Selasa',
            self::WEDNESDAY => 'Rabu',
            self::THURSDAY => 'Kamis',
            self::FRIDAY => 'Jumat',
            self::SATURDAY => 'Sabtu',
            self::SUNDAY => 'Minggu',
        };
    }

    public static function translate(string $dayInIndonesian): string
    {
        return match (strtolower($dayInIndonesian)) {
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

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $data = [];
        foreach (self::cases() as $case) {
            $data[$case->value] = $case->label();
        }

        return $data;
    }
}