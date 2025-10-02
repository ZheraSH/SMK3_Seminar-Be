<?php

namespace App\Enums;

enum RfidStatusEnum: string
{
    case USED = 'used';
    case NOTUSED = 'notused';

    public function label(): string
    {
        return match ($this) {
            self::USED => 'Sudah Digunakan',
            self::NOTUSED => 'Belum Digunakan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::USED => 'success',
            self::NOTUSED => 'danger',
        };
    }
}
