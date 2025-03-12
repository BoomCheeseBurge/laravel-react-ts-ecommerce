<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Enums\RolesEnum;
use App\Enums\OrderStatusEnum;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class VendorStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    
    // Place this widget on position 2 (based on the number widgets in the page)
    protected static ?int $sort = 2;

    // Determine how many seconds of interval should the stats be updated
    protected static ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        $filterBy = $this->filters['filterBy'] ?? 'week';
        
        $user_id = auth()->user()->id;

        $uniqueCustomers = $this->getTotalUniqueCustomers($user_id, $filterBy);

        return [
                Stat::make('Total (Unique) Customer', $uniqueCustomers)
                    ->description("Number of customers this $filterBy")
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('success'),

                Stat::make('Total Products', Product::where('created_by', $user_id)->count())
                    ->description("Number of products published by this vendor")
                    ->color('info'),
                    
                Stat::make('Draft Orders', Order::where('status', OrderStatusEnum::Draft->value)->count())
                    ->description("Number of orders checked-out but not paid yet"),
        ];
    }

    /**
     * Retrieve total unique customers of a specific vendor
     * 
     * @param int $user_id
     * @return int
     */
    private function getTotalUniqueCustomers(int $user_id, string $filterBy): int
    {
        $now = Carbon::now();

        return Order::query()
                    ->where('vendor_user_id', $user_id)
                    ->when($filterBy === 'week', function(Builder $query) use ($now) {
                        $startOfWeek = $now->copy()->startOfWeek();
                        $endOfWeek = $now->copy()->endOfWeek();
                
                        $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                    })
                    ->when($filterBy === 'month', function(Builder $query) use ($now) {
                        $startOfMonth = $now->copy()->startOfMonth();
                        $endOfMonth = $now->copy()->endOfMonth();
                
                        $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    })
                    ->when($filterBy === 'year', function(Builder $query) use ($now) {
                        $startOfYear = $now->copy()->startOfYear();
                        $endOfYear = $now->copy()->endOfYear();
                
                        $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
                    })
                    ->distinct('user_id')
                    ->count();
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
