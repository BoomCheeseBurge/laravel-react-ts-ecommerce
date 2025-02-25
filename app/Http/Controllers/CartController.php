<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CartService $cartService)
    {
        return Inertia::render('Cart/Index', [
            'cartItems' => $cartService->getGroupedCartItems(),
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

    public function checkout() {}
}
