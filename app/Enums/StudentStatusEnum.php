<?php

namespace App\Enums;

enum StudentStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case GRADUATED = 'graduated';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
            self::GRADUATED => 'Lulus',
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

    public static function isActive($status): bool
    {
        if ($status instanceof self) {
            return $status === self::ACTIVE;
        }
        
        return strtolower((string)$status) === strtolower(self::ACTIVE->value);
    }
}