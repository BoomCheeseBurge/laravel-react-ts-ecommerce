<?php

namespace App\Enums;

enum DeliveryStatusEnum: string
{
    case OrderReceived = 'OrderReceived';
    case OrderProcessed = 'OrderProcessed';
    case InDelivery = 'InDelivery';
    case Delivered = 'Delivered';
    case Cancelled = 'Cancelled';
    case Refunded = 'Refunded';

    public function label(): string
    {
        return match ($this) {
            self::OrderReceived => __('OrderReceived'),
            self::OrderProcessed => __('OrderProcessed'),
            self::InDelivery => __('InDelivery'),
            self::Delivered => __('Delivered'),
            self::Cancelled => __('Cancelled'),
            self::Refunded => __('Refunded'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_combine(self::values(), array_map(fn ($case) => $case->label(), self::cases()));
    }

    public function isStepActive(int $step): bool
    {
        return match ($step) {
            1 => in_array($this, [
                self::OrderReceived,
                self::OrderProcessed,
                self::InDelivery,
                self::Delivered,
            ]),
            2 => in_array($this, [
                self::OrderProcessed,
                self::InDelivery,
                self::Delivered,
            ]),
            3 => in_array($this, [
                self::InDelivery,
                self::Delivered,
            ]),
            4 => $this === self::Delivered,
            default => false,
        };
    }
}
