<?php

declare(strict_types=1);

namespace App\Enums;

enum WarrantyStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Released = 'released';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Released => 'Released',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'danger',
            self::Released => 'info',
            self::Cancelled => 'neutral',
        };
    }
}
