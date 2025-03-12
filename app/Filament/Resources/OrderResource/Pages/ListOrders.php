<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderCompletionStatus;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\OrderResource\Widgets\OrderOverview;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected ?string $subheading = 'ℹ️ Click on the table row for full view.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // Override the widgets of this resource page
    protected function getHeaderWidgets(): array
    {
        return [
            OrderOverview::class,
            OrderCompletionStatus::class,
        ];
    }
}
