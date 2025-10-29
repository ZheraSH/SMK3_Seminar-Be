<?php

namespace App\Enums;

enum ClassroomStudentStatusEnum: string
{
    case ACTIVE = 'active';
    case GRADUATED = 'graduated';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        return [
            self::ACTIVE->value => 'Aktif',
            self::GRADUATED->value => 'Lulus',
        ];
    }

    public function label(): string
    {
        return self::toArray()[$this->value] ?? 'Unknown';
    }
}