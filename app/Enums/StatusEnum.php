<?php

namespace App\Enums;

enum StatusEnum: string
{
    case COMPLETED = 'completed';
    case NOT_COMPLETED = 'not_completed';

    public function color(): string
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::NOT_COMPLETED => 'danger',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => 'Mengisi',
            self::NOT_COMPLETED => 'Tidak Mengisi',
        };
    }
}
