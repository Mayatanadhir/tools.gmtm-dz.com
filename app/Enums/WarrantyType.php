<?php

declare(strict_types=1);

namespace App\Enums;

enum WarrantyType: string
{
    case BidBond = 'bid_bond';
    case Performance = 'performance';
    case AdvancePayment = 'advance_payment';
    case Retention = 'retention';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BidBond => 'Bid Bond',
            self::Performance => 'Performance Bond',
            self::AdvancePayment => 'Advance Payment',
            self::Retention => 'Retention Bond',
            self::Other => 'Other',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::BidBond => 'primary',
            self::Performance => 'info',
            self::AdvancePayment => 'warning',
            self::Retention => 'neutral',
            self::Other => 'neutral',
        };
    }
}
