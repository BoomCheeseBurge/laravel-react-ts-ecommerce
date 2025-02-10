<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    // Override the title
    protected static ?string $title = "Product Variations";

    // Override the default hero icon
    protected static ?string $navigationIcon = 'heroicon-s-numbered-list';

    private function cartesianProduct($variationTypes, $defaultQuantity = null, $defaultPrice = null) {
        
        $result = [[]];

        // Loop over variation types (e.g., size)
        foreach ($variationTypes as $variationType) {

            $temp = [];

            // Loop over the options (e.g., small, medium, large)
            foreach ($variationType->options as $option) {

                // Add the current option to all existing combinations
                foreach ($result as $combination) {

                    $newCombination = $combination + [

                        'variation_type_' . $variationType->id => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'type' => $variationType->name,
                        ]
                    ];

                    // $temp will contain somewhat the following data:
                    // [                                    |
                    //     [                                |
                    //         'variation_type_1' => [      |
                    //             'id' => 1,               |
                    //             'name' => 'Black',       |
                    //             'type' => 'Color',       |
                    //         ]                            |
                    //     ],                               |
                    //     [                                |----------- $temp
                    //         'variation_type_1' => [      |
                    //             'id' => 2,   |           |
                    //             'name' => 'Blue',        |
                    //             'type' => 'Color',       |
                    //         ]                            |
                    //     ],                               |
                    // ]
                    //                  +
                    //     [                                |
                    //         'variation_type_1' => [      |
                    //             'id' => 3,               |
                    //             'name' => 'Purple',      |------------ $newCombination
                    //             'type' => 'Color',       |
                    //         ]                            |
                    //     ]                                |
                    $temp[] = $newCombination;
                }
            }

            // Update results with the variation type options
            $result = $temp;
        }

        foreach ($result as $combination) {

            /**
             * $combination is an array of supposedly two elements with keys containing the keyword 'variation_type_'
             * 
             *  $variationTypes is an array of two string elements
             */
            if (count($combination) === count($variationTypes)) {

                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
            }
        }

        return $result;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData): array {

        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;

        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergedResult = [];

        foreach ($cartesianProduct as $product) {

            // Extract option IDs from the current product combination as an array
            $optionIds = collect($product)
                                ->filter(fn ($value, $key) => Str::startsWith($key, 'variation_type_'))
                                ->map(fn ($option) => $option['id']) // Get only the IDs
                                ->values() // Creates a new collection and reset the keys of the collection
                                ->toArray(); // e.g., the stored array will be [1, 4]

            /**
             * Find matching entry in existing data
             * In other words, find the record associated with each variation combination within the array above
             */
            $match = array_filter($existingData, function ($existingOption) use ($optionIds) {

                return $existingOption['variation_type_option_ids'] === $optionIds;
            });

            // If there is a match is found, override the quantity and price values
            if(!empty($match)) {
                
                // Get the first record of the matching result (by moving to the first element in the '$match' array)
                $existingEntry = reset($match);

                // Provide ID for upsert upon updating the variations
                $product['id'] = $existingEntry['id'];

                // Assign the existing values of quantity and price
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            } else {

                // Otherwise, keep the default values of quantity and price
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;                
            }

            // Store the record into a temp array
            $mergedResult[] = $product;
        }

        // $mergedResult will contain somewhat the following data:
        // [
        //     [
        //         'variation_type_1' => [
        //             'id' => 1, 
        //             'name' => 'Black',
        //             'type' => 'Color',
        //         ],
        //         'variation_type_2' => [
        //             'id' => 1, 
        //             'name' => 'Small',
        //             'type' => 'Size',
        //         ],
        //          'id' => 1,
        //          'quantity' => 10,
        //          'price' => 10,
        //     ],
        //     [
        //         'variation_type_1' => [
        //             'id' => 2,
        //             'name' => 'Blue',
        //             'type' => 'Color',
        //         ],
        //         'variation_type_2' => [
        //             'id' => 1, 
        //             'name' => 'Small',
        //             'type' => 'Size',
        //         ],
        //          'id' => 2,
        //          'quantity' => 10,
        //          'price' => 10,
        //     ],
        //     [
        //         'variation_type_1' => [
        //             'id' => 3,
        //             'name' => 'Purple',
        //             'type' => 'Color',
        //         ],
        //         'variation_type_2' => [
        //             'id' => 1, 
        //             'name' => 'Small',
        //             'type' => 'Size',
        //         ],
        //          'id' => 3,
        //          'quantity' => 10,
        //          'price' => 10,
        //     ]
        // ]
        // ----------------------------------------------------------------
        // Note that ID may be null if there was no existing record of it
        // ----------------------------------------------------------------
        return $mergedResult;
    }

    protected function mutateFormDataBeforeFill(array $data): array {

        /**
         * $this->record refers to the EditRecord that refers to a product
         * The stored value here are related records from the 'product_variations' table
         */
        $variations = $this->record->variations->toArray();

        /**
         * 'variationTypes' contains the top level variation (e.g., size, color, etc.)
         */
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variations);

        return $data;
    }

    public function form(Form $form): Form {

        $types = $this->record->variationTypes;
        $fields = [];

        foreach ($types as $type) {

            // This field is non-editable by the user
            $fields[] = TextInput::make("variation_type_$type->id.id")->hidden();

            // This field displays the option name (instead of the variant type)
            $fields[] = TextInput::make("variation_type_$type->id.name")->label($type->name)->readOnly();
        }

        return $form->schema([
            Repeater::make('variations')
                    ->label(false)
                    ->collapsible()
                    ->addable(false)
                    ->defaultItems(1)
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        Section::make()
                            ->schema($fields)
                            ->columns(3),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric(true),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric(true),
                    ])
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array {

        // Initialize an array to hold the formatted data
        $formattedData = [];

        // Loop through each variation to re-structure the data
        foreach ($data['variations'] as $option) {

            // Store the variation type option ids (e.g., [1, 4])
            $variationTypeOptionIds = [];
            foreach ($this->record->variationTypes as $variationType) {

                /**
                 * ["variation_type_1": [
                 *      "id" => 1, <------------ This ID is inserted to the array below
                 *      "name" => "Black,
                 *      "type" => "Color,
                 * ]]
                 */

                $variationTypeOptionIds[] = $option["variation_type_$variationType->id"]['id'];
            }

            // Prepare the data structure for the data
            $formattedData[] = [
                'id' => $option["id"],
                'variation_type_option_ids' => $variationTypeOptionIds,
                'quantity' => $option["quantity"],
                'price' => $option["price"],
            ];
        }

        // Store the formatted data
        $data['variations'] = $formattedData;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model {
    
        // Assign the value to a temporary variable
        $variations = $data['variations'];
        // Unset the 'variations' key to prevent update error to the database since the table does not have 'variations' column
        unset($data['variations']);

        /**
         * Since upsert does not handle models directly but raw data,
         * the 'variation_type_option_ids' column needs to be handled as it is an array instead of JSON
         */
        $variations = collect($variations)->map(function ($variation) {

            // Specify all the columns, but convert 'variation_type_option_ids' to JSON
            return [
                'id' => $variation['id'],
                'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                'quantity' => $variation['quantity'],
                'price'=> $variation['price'],
            ];
        })->toArray();

        /**
         * Save the new variations to the database
         * where the records to be updated 
         * are uniquely identified by ID
         * The columns to be updated are also specified
         */
        $record->variations()->upsert($variations, 
                                        ['id'],
                                        [
                                                    'variation_type_option_ids',
                                                    'quantity',
                                                    'price'
                                                ]
                                    );

        /**
         * Return the record model since data is empty
         * and the only the variations relation is updated here
         */
        return $record;
    }
}
