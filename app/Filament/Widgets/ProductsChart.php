<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Product;
use App\Enums\RolesEnum;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    // Place this widget on position 6 (based on the number widgets in the page)
    protected static ?int $sort = 6;

    // Set the column span of the widget
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Monthly Published Product';

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
         
        return [
            'datasets' => [
                [
                    'label' => 'Product Items Created',
                    'data' => $data['productsPerMonth'],
                ]
            ],
            'labels' => $data['months'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();

        $productsPerMonth = [];

        $products = Product::without(['variationTypes', 'options', 'media'])->whereYear('created_at', $now->copy()->year)->get();

        $productsByMonth = $products->groupBy(function ($product) {

            return Carbon::parse($product->created_at)->format('n'); // Group by month number (1-12)
        });

        /**
         * When you pass a variable by reference (&), PHP passes a direct link or reference to the original variable.  
         * This means that the function or closure can directly modify the original variable.
         */
        $months = collect(range(1, 12))->map(function ($month) use ($now, $productsByMonth, &$productsPerMonth) {

            // $count = Product::whereMonth('created_at', 
            //                             // changes the month of the $now Carbon object to the given month value
            //                     Carbon::parse($now->month($month)
            //                             // narrow the scope of the date being searched
            //                             ->format('Y-m')
            //                     ))
            //                     ->count();

            /**
             * Count number of models based on key of $month
             */
            // If month key is not found, fallback to empty collection
            $count = $productsByMonth->get($month, collect())->count();

            // Store the products created within this specific month
            $productsPerMonth[] = $count;

            // Return the month value to be stored
            return $now->month($month)->format('M');
        })->toArray();

        return [
            'productsPerMonth' => $productsPerMonth,
            'months' => $months,
        ];
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
