<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\VariationTypeOption;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CartService
{
    private ?array $cachedCartItems = null;
    protected const COOKIE_NAME = 'cartItems';
    protected const COOKIE_LIFETIME = 60 * 24 * 365; // 1 year in seconds
    
    private static $callCount = 0;

    // -----------------------------------------------
    /**
     * 
     *   ███████ ███████ ██████  ██    ██ ██  ██████ ███████ ███████ 
     *   ██      ██      ██   ██ ██    ██ ██ ██      ██      ██      
     *   ███████ █████   ██████  ██    ██ ██ ██      █████   ███████ 
     *        ██ ██      ██   ██  ██  ██  ██ ██      ██           ██ 
     *   ███████ ███████ ██   ██   ████   ██  ██████ ███████ ███████ 
     *
     */

    public function addItemToCart(Product $product, int $quantity = 1, ?array $optionIds = null): void
    {
        /**
         * This if condition will also check against an empty array not just null
         * 
         * Note: This is for the case where the user adds item to cart from the product listing page instead of the product details page
         *       The user will be able to see the image of this variation type option on the product listing page
         */
        if (!$optionIds) {

            // Assign the first option ID from the product (only if option IDs are defined)
            $optionIds = $product->getFirstOptionsMap();
        }

        // Get either the variation type option or product price 
        $price = $product->getPriceForOptions($optionIds);

        if(Auth::check()) {

            $this->saveItemToDatabase($product->id, $quantity, $price, $optionIds);
        } else {
            $this->saveItemToCookies($product->id, $quantity, $price, $optionIds);
        }
    }

    public function updateItemQuantity(int $productId, int $quantity, ?array $optionIds = null): void
    {
        if (Auth::check()) {

            $this->updateItemQuantityInDatabase($productId, $quantity, $optionIds);
        } else{

            $this->updateItemQuantityInCookies($productId, $quantity, $optionIds);
        }
    }

    public function removeItemFromCart(int $productId, ?array $optionIds = null): void
    {
        if (Auth::check()) {

            $this->removeItemFromDatabase($productId, $optionIds);
        } else {

            $this->removeItemFromCookies($productId, $optionIds);
        }
    }

    // -----------------------------------------------
    /**
     * 
     *   ██   ██ ███████ ██      ██████  ███████ ██████  
     *   ██   ██ ██      ██      ██   ██ ██      ██   ██ 
     *   ███████ █████   ██      ██████  █████   ██████  
     *   ██   ██ ██      ██      ██      ██      ██   ██ 
     *   ██   ██ ███████ ███████ ██      ███████ ██   ██ 
     *
     */

    public function getCartItems(): array
    {
        try {
            // Cart item has not been cached yet
            if($this->cachedCartItems === null) {

                // For authenticated users, retrieve cart items from the database
                if(Auth::check()) {
                    // self::$callCount++;

                    // if (self::$callCount > 1) {
                    //     // Function has been called more than once
                    //     Log::info('myFunction called more than once. Call count: ' . self::$callCount);
                    // }
                    $cartItems = $this->getCartItemsFromDatabase();
                    
                // Otherwise, retrieve cart items from the cookies
                } else {
                    $cartItems = $this->getCartItemsFromCookies();
                }

                // If the cart item is empty, do not proceed further
                if(empty($cartItems)) return [];

                /**
                 * Retrieve the product IDs from the cart items
                 * 
                 * Note: cartItems are returned as an array above
                 */
                $productIds = collect($cartItems)->map(fn($item) => $item['product_id']);

                // Retrieve the products associated with the product IDs
                $products = Product::whereIn('id', $productIds)
                                    ->without(['variationTypes', 'options'])
                                    ->with([
                                            'user.vendor',
                                            'media' => function (MorphMany $query) {
                                                $query->where('collection_name', 'images');
                                            }
                                        ])
                                    ->forWebsite()
                                    ->get()
                                    ->keyBy('id');

                $cartItemData = [];

                // dd($cartItems);

                foreach ($cartItems as $cartItem) {

                    // Retrieve a single product based on the cart item of the current loop 
                    $product = data_get($products, $cartItem['product_id']);

                    /**
                     * If product not found, continue to the next cart item
                     * 
                     * Note: the product either got deleted or unpublished when the product was added to the cart
                     */
                    if (!$product) continue;

                    // dd($cartItem['option_ids']);

                    // Retrieve the variation type options
                    $optionInfo = [];
                    $options = VariationTypeOption::with('variationType')->whereIn('id', $cartItem['option_ids'])->get()->keyBy('id');

                    // Retrieve the image URL
                    $imageUrl = null;

                    foreach ($cartItem['option_ids'] as $option_id) {

                        $option = data_get($options, $option_id);

                        // Check if the image URL was previously assigned
                        if(!$imageUrl) {

                            // Assign the image URL from the option that has an associated image
                            $imageUrl = $option->getFirstMediaUrl('images', 'small');
                        }

                        // Append variation type option information to this array
                        $optionInfo[] = [
                            'id' => $option->id,
                            'name' => $option->name,
                            'type' => [
                                'id' => $option->variationType->id,
                                'name' => $option->variationType->name,
                            ],
                        ];
                    }

                    // Populate item cart information
                    $cartItemData[] = [
                        'id' => $cartItem['id'],
                        'product_id' => $product->id,
                        'title' => $product->title,
                        'slug' => $product->slug,
                        'price' => $cartItem['price'],
                        'quantity' => $cartItem['quantity'],
                        'option_ids' => $cartItem['option_ids'],
                        'options' => $optionInfo,
                        'image' => $imageUrl ?: $product->getFirstMediaUrl('images', 'small'),
                        'user' => [
                            'id' => $product->created_by,
                            'name' => $product->user->vendor->store_name,
                        ],
                    ];
                }

                // Cache the cart item
                $this->cachedCartItems = $cartItemData;
            }

            return $this->cachedCartItems;

        } catch (\Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        // Return empty array in case of an exception
        return [];
    }

    public function getTotalQuantity(array $cachedCartItems): int
    {
        $totalQuantity = 0;

        foreach ($cachedCartItems as $item) {

            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }
    
    public function getTotalPrice(array $cachedCartItems): int
    {
        $totalPrice = 0;

        foreach ($cachedCartItems as $item) {

            $totalPrice += $item['price'];
        }

        return $totalPrice;
    }

    /**
     * Return the grouped cart items based on the vendor
     * 
     * @return array
     */
    public function getGroupedCartItems(array $cartItems): array
    {
        /**
         * The returned array has the following format:
         * 
         * [
         *      2 => [                    <- user ID or vendor ID
         *         'user' => [],
         *         'items' => [],
         *         'totalQuantity' => 1,
         *         'totalPrice' => 1,
         *      ],
         * 
         *      3 => [                    <- user ID or vendor ID
         *         'user' => [],
         *         'items' => [],
         *         'totalQuantity' => 1,
         *         'totalPrice' => 1,
         *      ],
         * ]
         */
        return collect($cartItems)
                // Cart items are grouped by vendor
                ->groupBy(fn($item) => $item['user']['id'])
                // Map the grouped items of each vendor
                ->map(fn($items, $userId) => [
                    'user' => $items->first()['user'],
                    'items' => $items->toArray(), // Convert the grouped vendor items into an array
                    'totalQuantity' => $items->sum('quantity'),
                    'totalPrice' => $items->sum(fn($item) => $item['price'] * $item['quantity']),
                ])
                ->toArray();
    }

    // ----------------------------------------------------------------------------
    /**
     * 
     *   ______  _______ _______ _______ ______  _______ _______ _______
     *   |     \ |_____|    |    |_____| |_____] |_____| |______ |______
     *   |_____/ |     |    |    |     | |_____] |     | ______| |______
     *
     */

    /**
     * Retrieve cart items from the database
     * 
     * @return array
     */
    protected function getCartItemsFromDatabase(): array
    {
        $cartItems = CartItem::where('user_id', auth()->user()->id)
                            ->get()
                            ->map(function ($cartItem) {
                                return [
                                    'id' => $cartItem->id,
                                    'product_id' => $cartItem->product_id,
                                    'quantity' => $cartItem->quantity,
                                    'price' => $cartItem->price,
                                    'option_ids' => $cartItem->variation_type_option_ids,
                                ];
                            })
                            ->toArray();

        return $cartItems;
    }

    /**
     * Update the cart item quantity from the cart page
     * 
     * @param int $productId
     * @param int $quantity
     * @param array $optionIds
     * @return void
     */
    protected function updateItemQuantityInDatabase(int $productId, int $quantity, array $optionIds): void
    {
        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        $cartItem = CartItem::where('user_id', auth()->user()->id)
                            ->where('product_id', $productId)
                            ->where('variation_type_option_ids', json_encode($optionIds))
                            ->first();

        // Update cart item if it is found in the database
        if ($cartItem) {
            $cartItem->update([
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Add a new cart item to the database 
     * OR
     * update the quantity of an existing cart item in the database (if the user clicks add to cart button on the same product variation again)
     * 
     * @param int $productId
     * @param int $quantity
     * @param array $optionIds
     * @return void
     */
    protected function saveItemToDatabase(int $productId, int $quantity, int $price, array $optionIds): void
    {
        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        $cartItem = CartItem::where('user_id', auth()->user()->id)
                            ->where('product_id', $productId)
                            ->where('variation_type_option_ids', json_encode($optionIds))
                            ->first();

        // Update the cart item if it exists in the database
        if ($cartItem) {

            $cartItem->update([
                // Sum the existing quantity with the new quantity
                'quantity' => DB::raw("quantity + $quantity"),
            ]);

        // Else, add the cart item to the database
        } else {
            CartItem::create([
                'user_id' => auth()->user()->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'variation_type_option_ids' => $optionIds,
            ]);
        }
    }

    /**
     * Remove cart item from the database
     * 
     * @param int $productId
     * @param array $optionIds
     * @return void
     */
    protected function removeItemFromDatabase(int $productId, array $optionIds): void
    {
        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        $cartItem = CartItem::where('user_id', auth()->user()->id)
                            ->where('product_id', $productId)
                            ->where('variation_type_option_ids', json_encode($optionIds))
                            ->delete();
    }

    // -----------------------------------------------------------------------
    /**
     * 
     *   _______  _____   _____  _     _ _____ _______ _______
     *   |       |     | |     | |____/    |   |______ |______
     *   |_____  |_____| |_____| |    \_ __|__ |______ ______|
     *
     */

    /**
     * Retrieve cart items from the cookies
     * 
     * @return array
     */
    protected function getCartItemsFromCookies(): array
    {
        $cartItems = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);

        return $cartItems;
    }
    
    /**
     * Update the cart item quantity from the cart page
     * 
     * @param int $productId
     * @param int $quantity
     * @param array $optionIds
     * @return void
     */
    protected function updateItemQuantityInCookies(int $productId, int $quantity, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();

        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        // Create a unique key based on the product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        // Check if the unique key exists in the cart item
        if (isset($cartItems[$itemKey])) {

            // Update the cart item's quantity
            $cartItems[$itemKey]['quantity'] = $quantity;
        }

        // Save updated cart items back to the cookies
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }
    
    /**
     * Add a new cart item to the database 
     * OR
     * update the quantity of an existing cart item in the database (if the user clicks add to cart button on the same product variation again)
     * 
     * @param int $productId
     * @param int $quantity
     * @param int $price
     * @param array $optionIds
     * @return void
     */
    protected function saveItemToCookies(int $productId, int $quantity, int $price, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();

        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        // Create a unique key based on the product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        // Check if the unique key exists in the cart item
        if (isset($cartItems[$itemKey])) {

            // Update the cart item's quantity
            $cartItems[$itemKey]['quantity'] += $quantity;
            // Update the price (if the product price changed somehow)
            $cartItems[$itemKey]['price'] = $price;
        
        // Else, add the cart item to the cookies
        } else {
            $cartItems[$itemKey] = [
                'id' => Str::uuid(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price,
                'option_ids' => $optionIds,
            ];
        }

        // Save updated cart items back to the cookies
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }
    
    /**
     * Remove cart item from the cookies
     * 
     * @param int $productId
     * @param int $quantity
     * @param array $optionIds
     * @return void
     */
    protected function removeItemFromCookies(int $productId, array $optionIds): void
    {
        $cartItems = $this->getCartItemsFromCookies();

        // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
        ksort($optionIds);

        // Create a unique key based on the product ID and option IDs
        $itemKey = $productId . '_' . json_encode($optionIds);

        // Remove the item from the cart
        unset($cartItems[$itemKey]);

        // Save updated cart items back to the cookies
        Cookie::queue(self::COOKIE_NAME, json_encode($cartItems), self::COOKIE_LIFETIME);
    }

    public function moveCartItemsToDatabase(int $userId): void
    {
        // Retrieve the cart items from the cookies
        $cartItems = $this->getCartItemsFromCookies();

        // Loop through every cart item and insert them to the database
        foreach ($cartItems as $item) {
            /**
             * Check if the item exists in the database for the user.
             * In cases where the user may have added the same item while logged in.
             */
            $existingItem = CartItem::where('user_id', $userId)
                                ->where('product_id', $item['product_id'])
                                ->where('variation_type_option_ids', json_encode($item['option_ids']))
                                ->first();
            
            // If the same product is already recorded in the database, update its quantity and (possibly) price
            if ($existingItem)
            {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $item['quantity'],
                    'price' => $item['price'],
                ]);

            // Else, create a new record for this item
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'variation_type_option_ids' => $item['option_ids'],
                ]);
            }

            // Empty the cart items in the cookies
            Cookie::queue(self::COOKIE_NAME, '', -1);
        }
    }
}
