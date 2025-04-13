<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryStatusEnum;
use Stripe\Stripe;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Services\CartService;
use App\Enums\OrderStatusEnum;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {  
        // Check for logged-in user
        if(auth()->user())
        {
            // Get stale orders from this user
            $orders = Order::where('user_id', auth()->user()->id)
                        ->where('status', OrderStatusEnum::Draft->value)
                        ->get();
                        
            // Clear previous draft orders made
            foreach ($orders as $order) {
                $order->delete(); // This will trigger the deleting event for its corresponding order items
            }
        }

        $sharedData = Inertia::getShared();

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartService->getGroupedCartItems($sharedData['cartItems'] ?? []),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Product $product, CartService $cartService)
    {
        $request->mergeIfMissing([
            'quantity' => 1
        ]);

        /**
         * Assumed that if 'option_ids' is not provided, then the product does not have any variation
         */
        $validatedData = $request->validate([
            'option_ids' => 'nullable|array',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartService->addItemToCart($product, $validatedData['quantity'], $validatedData['option_ids'] ?: []);

        return back()->with('success', 'Added item to cart successfully!');
    }

    /**
     * Update the quantity of the item within the cart.
     */
    public function update(Request $request, Product $product, CartService $cartService)
    {
        $request->validate([
            'quantity' => 'integer|min:1',
        ]);

        /**
         * Determine which product variation option to update
         * 
         * Note: if product has no variation, assign empty array
         */
        $optionIds = $request->input('option_ids') ?: [];
        // Get the updated quantity
        $quantity = $request->input('quantity');

        $cartService->updateItemQuantity($product->id, $quantity, $optionIds);

        return back()->with('success', 'Updated item quantity from cart successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product, CartService $cartService)
    {
        /**
         * Determine which product variation option to update
         */
        $optionIds = $request->input('option_ids');

        $cartService->removeItemFromCart($product->id, $optionIds);

        return back()->with('success', 'Removed item from cart successfully!');
    }

    /**
     * Set checkout later for the item within the cart.
     */
    public function checkoutLater(Request $request, Product $product, CartService $cartService)
    {
        /**
         * Determine which product variation option to update
         * 
         * Note: if product has no variation, assign empty array
         */
        $optionIds = $request->input('option_ids') ?: [];

        $cartService->checkoutLater($product->id, $optionIds);

        return back()->with('success', 'Cart item saved for later.');
    }

    public function checkout(StoreAddressRequest $request, CartService $cartService) {

        $validatedData = $request->validated();

        if($request->input('save_address'))
        {
            Address::updateOrCreate(
            ['user_id' => auth()->user()->id],
                [
                    'full_name' => $validatedData['fullname'],
                    'phone_number' => $validatedData['phone_number'],
                    'address_line_1' => $validatedData['address_line_1'],
                    'address_line_2' => $validatedData['address_line_2'],
                    'city' => $validatedData['city'],
                    'province' => $validatedData['province'],
                    'postal_code' => $validatedData['postal_code'],
                ]
            );
        }

        Stripe::setApiKey(config('app.stripe_secret_key'));

        // A vendor ID is only provided if the user (or customer) decides to only purchase item(s) from a specific vendor from the checkout page
        $vendorId = $request->input('vendor_id');

        // Get the Inertia shared data
        $sharedData = Inertia::getShared();

        // Get the cart items from the shared data
        $cartItems = $sharedData['cartItems'];

        // Filter out cart items that is to be checked out later
        $cartItems = array_filter($cartItems, fn ($item) => $item['checkout_later'] !== 1);

        /**
         * Has data structure:
         * 
         * [
         *  1 => [],
         *  2 => [],
         * ]
         */
        $groupedCartItems = $cartService->getGroupedCartItems($cartItems);

        DB::beginTransaction();
        
        try {
            $checkoutCartItems = $groupedCartItems;

            // Check if the checkout is only for a single vendor
            if ($vendorId)
            {
                /**
                 * Retrieve the cart items belonging to this specific vendor
                 * 
                 * Has data structure:
                 * 
                 * [
                 *  []
                 * ]
                 */
                $checkoutCartItems = [$groupedCartItems[$vendorId]];
            }

            // For model record
            $orders = [];
            // For Stripe instance
            $lineItems = [];

            // Loop through grouped cart items of each vendor
            foreach ($checkoutCartItems as $items) {
                
                $user = $items['user'];
                $cartItems = $items['items'];

                // Create an order for the vendor of the current item
                $order = Order::create([
                    'stripe_session_id' => null,
                    'user_id' => $request->user()->id,
                    'vendor_user_id' => $user['id'],
                    'total_price' => $items['totalPrice'],
                    'status' => OrderStatusEnum::Draft->value,
                ]);

                $orders[] = $order;

                // Loop through the items from this specific vendor
                foreach ($cartItems as $item) {
                    
                    // Insert order item record
                    $order = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'variation_type_option_ids' => $item['option_ids'],
                    ]);

                    // Insert delivery record for the order item
                    Delivery::create([
                        'order_id' => $order->id,
                        'date' => null,
                        'status' => DeliveryStatusEnum::OrderReceived->value,
                        'full_name' => $validatedData['full_name'],
                        'phone_number' => $validatedData['phone_number'],
                        'address_line_1' => $validatedData['address_line_1'],
                        'address_line_2' => $validatedData['address_line_2'],
                        'city' => $validatedData['city'],
                        'province' => $validatedData['province'],
                        'postal_code' => $validatedData['postal_code'],
                        'instructions' => $validatedData['instructions'],
                    ]);

                    // Get the variation option description
                    $description = collect($item['options'])->map(function ($item) {
                        return "{$item['type']['name']}: {$item['name']}";
                    })->implode(', ');

                    // To be purchased item that will be informed to Stripe
                    $lineItem = [
                        'price_data' => [
                            'currency' => config('app.currency'),
                            'product_data' => [
                                'name' => $item['title'],
                                'images' => [$item['image']], // Take the first image
                            ],
                            'unit_amount' => $item['price'] * 100, // cent unit
                        ],
                        'quantity' => $item['quantity'],
                    ];

                    // Just in case the product does NOT have variation options
                    if ($description) {
                        $lineItem['price_data']['product_data']['description'] = $description;
                    }

                    $lineItems[] = $lineItem;
                }
            }

            // Create a Stripe checkout session
            $session = Session::create([
                'customer_email' => $request->user()->email,
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('stripe.success', []) . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => route('stripe.failure', []),
            ]);

            // Update the Stripe ID for every order
            foreach ($orders as $order) {
                
                $order->stripe_session_id = $session->id;
                $order->save();
            }

            DB::commit();

            // Redirect to the Stripe payment page
            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollback();

            return back()->with('error', $e->getMessage() ?: 'Something went wrong.');
        }
    }
}
