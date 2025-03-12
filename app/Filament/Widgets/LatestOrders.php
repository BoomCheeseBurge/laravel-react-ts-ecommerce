<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use App\Enums\RolesEnum;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\OrderResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    // Place this widget on position 4 (based on the number widgets in the page)
    protected static ?int $sort = 4;

    // Set the column span of the widget
    protected int | string | array $columnSpan = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([ 
                TextColumn::make('id')
                        ->label('Order No.')
                        ->searchable()
                        ->sortable()
                        ->toggleable(),
                TextColumn::make('total_price')
                        ->state(function (Order $record): string {
                            return Number::currency($record->total_price);
                        })
                        ->searchable()
                        ->sortable(),
                TextColumn::make('status')
                        ->searchable()
                        ->sortable(),
                TextColumn::make('created_at')
                        ->label('Order Date')
                        ->date(),
            ]);
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
