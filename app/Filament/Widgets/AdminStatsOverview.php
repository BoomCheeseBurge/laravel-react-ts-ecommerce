<?php

namespace App\Filament\Widgets;

use App\Enums\RolesEnum;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::with('roles')->whereNot('name', RolesEnum::Admin->value)->count())
            ->description('Registered users in application')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success')
        ];
    }

    /**
     * Only admin can view this widget
     * 
     * @return bool
     */
    public static function canView(): bool
    {
        return auth()->user()->hasRole(RolesEnum::Admin);
    }
}
