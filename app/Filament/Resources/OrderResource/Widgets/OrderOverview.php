<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class OrderOverview extends BaseWidget
{    
    protected function getStats(): array
    {
        $user_id = auth()->user()->id;
        
        return [
            Stat::make('Total orders', Order::where('vendor_user_id', $user_id)->count()),

            Stat::make('Pending Orders', Order::where('vendor_user_id', $user_id)
                ->where('status', OrderStatusEnum::Draft->value)
                ->count()),
            
            Stat::make('Paid Orders', Order::where('vendor_user_id', $user_id)
                ->where('status', OrderStatusEnum::Paid->value)
                ->count()),
        ];
    }
}
