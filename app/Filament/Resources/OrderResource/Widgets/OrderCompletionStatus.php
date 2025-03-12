<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrderCompletionStatus extends BaseWidget
{
    protected function getStats(): array
    {
        $user_id = auth()->user()->id;

        return [
            Stat::make('Delivered Orders', Order::where('vendor_user_id', $user_id)
                ->where('status', OrderStatusEnum::Delivered->value)
                ->count()),

            Stat::make('Shipped Orders', Order::where('vendor_user_id', $user_id)
                ->where('status', OrderStatusEnum::Shipped->value)
                ->count()),
            
            Stat::make('Cancelled Orders', Order::where('vendor_user_id', $user_id)
                ->where('status', OrderStatusEnum::Cancelled->value)
                ->count()),
        ];
    }
}
