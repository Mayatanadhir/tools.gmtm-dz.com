<?php

declare(strict_types=1);

namespace App\Enums;

enum CalibrationCertificateStatus: string
{
    case Draft = 'draft';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case ExpiringSoon = 'expiring_soon';
    case Expired = 'expired';
    case Archived = 'archived';

    /**
     * Get translated label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::UnderReview => __('Under Review'),
            self::Approved => __('Approved / Valid'),
            self::ExpiringSoon => __('Expiring Soon'),
            self::Expired => __('Expired'),
            self::Archived => __('Archived / Superseded'),
        };
    }

    /**
     * Get semantic badge variant.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::UnderReview => 'warning',
            self::Approved => 'success',
            self::ExpiringSoon => 'warning',
            self::Expired => 'danger',
            self::Archived => 'neutral',
        };
    }

    /**
     * Determine if the certificate is legally/operationally valid.
     */
    public function isValid(): bool
    {
        return in_array($this, [self::Approved, self::ExpiringSoon], true);
    }

    /**
     * Get all values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
