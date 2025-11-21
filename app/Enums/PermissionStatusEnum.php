<?php

namespace App\Enums;

enum PermissionStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
    
    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }
    
    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}