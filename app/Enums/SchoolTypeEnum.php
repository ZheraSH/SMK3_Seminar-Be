<?php

namespace App\Enums;

enum SchoolTypeEnum: string
{
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';
    case SMK = 'smk';
    case MA = 'ma';

    public function label(): string
    {
        return match ($this) {
            self::SD => 'SD',
            self::SMP => 'SMP', 
            self::SMA => 'SMA',
            self::SMK => 'SMK',
            self::MA => 'MA',
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
