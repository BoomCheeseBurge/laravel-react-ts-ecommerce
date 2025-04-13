<?php

namespace App\Infolists\Components;

use App\Models\OrderItem;
use Filament\Infolists\Components\Entry;

class DeliveryStatus extends Entry
{
    protected string $view = 'infolists.components.delivery-status';

    public function getState(): array
    {
        $orderAddresses = OrderItem::select(['deliveries.id', 'deliveries.order_id', 'deliveries.status'])
                                    ->where('order_items.order_id', $this->getRecord()->id)
                                    ->join('deliveries', 'order_items.id', '=', 'deliveries.order_id')
                                    ->get();

        return [
            $orderAddresses,
        ];
    }
}
