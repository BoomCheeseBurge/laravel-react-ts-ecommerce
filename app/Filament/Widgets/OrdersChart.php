<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Enums\RolesEnum;
use App\Enums\OrderStatusEnum;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    // Place this widget on position 4 (based on the number widgets in the page)
    protected static ?int $sort = 4; // In the same row as the products chart

    // Set the column span of the widget
    protected int | string | array $columnSpan = 3;

    protected static ?string $heading = 'Order Status';

    protected function getData(): array
    {
        $data = Order::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status')
                    ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => array_values($data)
                ]
            ],
            'labels' => OrderStatusEnum::cases(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Only vendor can view this widget
     * 
     * @return bool
     */
    public static function canView(): bool
    {
        return auth()->user()->hasRole(RolesEnum::Vendor);
    }
}
