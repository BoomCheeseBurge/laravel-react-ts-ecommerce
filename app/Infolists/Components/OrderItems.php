<?php

namespace App\Infolists\Components;

use App\Models\OrderItem;
use App\Models\VariationTypeOption;
use Filament\Infolists\Components\Entry;
use Illuminate\Support\Arr;

class OrderItems extends Entry
{
    protected string $view = 'infolists.components.order-items';

    public function getState(): array
    {
        $orderItems = OrderItem::where('order_id', $this->getRecord()->id)->get();

        $optionIds = $orderItems->flatMap(function ($orderItem) {
                        return array_values($orderItem->variation_type_option_ids);
                    })->toArray();

        $variationOptions = VariationTypeOption::whereIn('id', $optionIds)->get();

        $images = [];

        foreach ($variationOptions->loadMissing('media') as $option) {

            $images[] = $option->getFirstMediaUrl('images');
        }

        return [
            $orderItems,
            $variationOptions->keyBy('id'),
            Arr::flatten($images),
        ];
    }
}
