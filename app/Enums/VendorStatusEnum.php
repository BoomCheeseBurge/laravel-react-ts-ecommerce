<?php

namespace App\Enums;

enum VendorStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
        };
    }

    public function labels(): array
    {
        return [
            self::Pending->value => __('Pending'),
            self::Approved->value => __('Approve'),
            self::Rejected->value => __('Rejected'),
        ];
    }

    // For differentiating vendor status in filament admin panel
    public function color(): array
    {
        return [
            'gray' => self::Pending->value,
            'success' => self::Approved->value,
            'danger' => self::Rejected->value,
        ];
    }
}
