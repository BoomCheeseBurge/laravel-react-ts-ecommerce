<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Product;
use App\Enums\RolesEnum;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use App\Models\VariationTypeOption;
use Illuminate\Database\Eloquent\Builder;

class PopularProductsWidget extends ChartWidget
{
    protected static ?string $heading = 'Popular Product';

    // Place this widget on position 5 (based on the number widgets in the page)
    protected static ?int $sort = 5;

    // Set the column span of the widget
    protected int | string | array $columnSpan = 'full';
    // Set the column start of the widget
    // protected int | string | array $columnStart = 2;

    // Define the filter for the widget
    public ?string $filter = 'week';

    // customize the color of the chart data
    protected static string $color = 'info';

    // Return an array of values and labels for your filter
    protected function getFilters(): ?array
{
    return [
        'week' => 'This week',
        'month' => 'This month',
        'year' => 'This year',
    ];
}

    protected function getData(): array
    {
        $user_id = auth()->user()->id;

        // Get the value of the filter
        $activeFilter = $this->filter;

        // Get the current date and time
        $now = Carbon::now();

        // Get products and variations that belongs to the vendor
        $products =  Product::without(['media', 'options', 'variationTypes'])->select('id', 'title', 'created_by')
                    ->where('created_by', $user_id)
                    ->with('variations')
                    ->get();

        $data = [];
        $labels = [];

        foreach ($products as $product) {
            
            // Check if the product has variations
            if ($product->variations->isNotEmpty())
            {
                $product->loadMissing('variations');

                // Fetch the order items of the product that have been paid
                $orderItems = OrderItem::where('product_id', $product->id)
                                        ->whereHas('order', function(Builder $query) use ($activeFilter, $now) {
                                            $query->where('status', 'paid')
                                                ->when($activeFilter === 'week', function(Builder $query) use ($now) {

                                                    $startOfWeek = $now->copy()->startOfWeek();
                                                    $endOfWeek = $now->copy()->endOfWeek();
        
                                                    return $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                                                })
                                                ->when($activeFilter === 'month', function(Builder $query) use ($now) {
        
                                                    $startOfMonth = $now->copy()->startOfMonth();
                                                    $endOfMonth = $now->copy()->endOfMonth();
        
                                                    return $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                                                })
                                                ->when($activeFilter === 'year', function(Builder $query) use ($now) {
        
                                                    $startOfYear = $now->copy()->startOfYear();
                                                    $endOfYear = $now->copy()->endOfYear();
        
                                                    return $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
                                                });
                                        })
                                        ->get();

                // Fetch the unique IDs of the variation type option of the product
                $variationTypeOptionIds = $product->variations->flatMap(function ($variation) {
                                                                return $variation->variation_type_option_ids;
                                                            })->unique()->toArray();
                
                // Get the IDs and names of the variation type option of the product
                $variationTypeOptions = VariationTypeOption::select('id', 'name')->whereIn('id', $variationTypeOptionIds)->get()->keyBy('id');

                // Count orders for each product variation option
                foreach ($product->variations as $variation) {
                    
                    $variationOrderCount = $orderItems->filter(function($orderItem) use ($variation) {

                                                        return array_values($orderItem->variation_type_option_ids) === $variation->variation_type_option_ids;
                                                    })
                                                    ->count();

                    $variationOptionNames = collect($variation->variation_type_option_ids)->map(function ($optionId) use ($variationTypeOptions) {

                        return $variationTypeOptions->get($optionId)->name ?? '';
                    })->implode('-');
                                        
                    $labels[] = preg_replace('/\s+/', '-', $product->title) . '-' . $variationOptionNames;
                    $data[] = $variationOrderCount;
                }
            } else {
                // Count orders for the base product
                $productOrderCount = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function(Builder $query) {
                    $query->where('orders.status', 'paid');
                })
                ->count();

                $labels[] = $product->title;
                $data[] = $productOrderCount;
            }
        }
    
        return [
            'datasets' => [
                [
                    'label' => 'Product Sales',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
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
