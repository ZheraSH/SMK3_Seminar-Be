<?php

namespace App\Enums;

enum TapTypeEnum: string
{
    case CHECKIN = 'checkin';
    case CHECKOUT = 'checkout';
    case CLASS_CHECKIN = 'class_checkin';
    
    public function label(): string
    {
        return match($this) {
            self::CHECKIN => 'Absen Masuk',
            self::CHECKOUT => 'Absen Pulang',
            self::CLASS_CHECKIN => 'Absen Kelas',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->label();
        }
        return $array;
    }
}