<?php

namespace App\Http\Controllers;

use Stripe\Webhook;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\CartItem;
use Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderViewResource;
use Illuminate\Support\Facades\Log;
use Inertia\Response as InertiaResponse;

class StripeController extends Controller
{
    public function success(Request $request): InertiaResponse
    {
        $user = auth()->user();
        $session_id = $request->get('session_id');
        $orders = Order::where('stripe_session_id', $session_id)->get();

        // If somehow the orders are not found in the database
        if ($orders->count() == 0) {
            abort(404);
        }

        // If somehow the order does NOT belong to the user who purchased the order, abort immediately
        foreach ($orders as $order) {
            if ($order->user_id !== $user->id) {
                abort(404);
            }
        }

        return Inertia::render('Stripe/Success', [
            'orders' => OrderViewResource::collection($orders)->collection->toArray(),
        ]);
    }

    public function failure(): InertiaResponse
    {
        return Inertia::render('Stripe/Failure');
    }

    public function webhook(Request $request): Response
    {
        $client = new StripeClient(config('app.stripe_secret_key'));

        $endpoint_secret = config('app.stripe_webhook_key');

        // Get the payload of the payment request
        $payload = $request->getContent();
        // To later verify the signature header
        $sig_header = $request->header('Stripe-Signature');
        // Store Stripe event
        $event = null;

        try {
            // Validate and create the event 
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error($e);

            // Invalid payload response
            return response('Invalid Payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error($e);

            // Invalid payload response
            return response('Invalid Payload', 400);
        }

        // Print the successful event webhook response
        Log::info('==================================');
        Log::info('==================================');
        Log::info($event->type);
        Log::info($event);

        /**
         * There is around 4-5 events received, but only 2 of those are required to be handled.
         * 
         * Note: 'checkout.session.completed' will be received as a webhook response followed by 'charge.updated' during the Stripe payment process
         */
        switch ($event->type) {
            // Informs the Stripe transaction fee
            case 'charge.updated':
                $charge = $event->data->object;
                $transactionId = $charge['balance_transaction'];
                $paymentIntent = $charge['payment_intent'];

                // Send a request to the Stripe API to receive the balance transaction information
                $balanceTransaction = $client->balanceTransactions->retrieve($transactionId);

                $orders = Order::where('payment_intent', $paymentIntent)->get();

                // Equivalent to the total price of all the orders
                $totalAmount = $balanceTransaction['amount'];
                // Platform transaction fee
                $stripeFee = 0;

                foreach ($balanceTransaction['fee_details'] as $fee_detail) {
                    // Get the Stripe fee (in cents)
                    if ($fee_detail['type'] === 'stripe_fee') {
                        $stripeFee = $fee_detail['amount'];
                    }
                }

                // Calculate platform fee percentage
                $platformFeePercent = config('app.platform_fee_pct');

                foreach ($orders as $order) {
                    /**
                     * Vendor 1
                     * 10,000¢
                     * 
                     * Vendor 2
                     * 20,000¢
                     * 
                     * vendorShareForVendor1 = 10,000¢ / 30,000¢
                     * vendorShareForVendor2 = 20,000¢ / 30,000¢
                     */
                    $vendorShare = $order->total_price / $totalAmount;

                    /**
                     * The Stripe fee is obtained in the foreach loop right before this one.
                     * Stripe fee for each vendor can be calculated in the following example (continuing from above):
                     * 
                     * Stripe Fee (Vendor 1)
                     * 1/3 (vendor share) * 900¢ (Stripe fee) = 300¢
                     */
                    $order->online_payment_commission = $vendorShare * $stripeFee;

                    /**
                     * [Continuing from the example above]
                     * 
                     * Total price = ((10,000¢ [total price for this Vendor 1] - 300¢ [online payment commission]) / 100) * 10/100 [platform fee percentage] = 970¢
                     */
                    $order->website_commission = ($order->total_price - $order->online_payment_commission) / 100 * $platformFeePercent;

                    /**
                     * [Continuing from the example above]
                     * 
                     * Vendor Subtotal
                     * 10,000¢ [total price for Vendor 1] - 300¢ [online payment commission] - 970¢ [website commission] = 8,730¢ (or $87.3)
                     */
                    $order->vendor_subtotal = $order->total_price - $order->online_payment_commission - $order->website_commission;

                    // Save the updated order information in the database
                    $order->save();
                }
                break;
            
            // Payment was completed without any issue 
            case 'checkout.session.completed':
                
                $session = $event->data->object;
                $paymentIntent = $session['payment_intent'];

                // Retrieve the orders based on the session ID
                $orders = Order::with(['orderItems'])->where(['stripe_session_id' => $session['id']])->get();

                $productsToBeDeletedFromCart = [];

                // Loop through every orders
                foreach ($orders as $order) {
                    // Update the payment intent for every orders (and their order status)
                    $order->payment_intent = $paymentIntent;
                    $order->status = OrderStatusEnum::Paid;
                    $order->save();

                    // Store the to be deleted paid items from the cart in the database
                    $productsToBeDeletedFromCart = [
                        // Destructure the existing cart items
                        ...$productsToBeDeletedFromCart,
                        ...$order->orderItems->map(fn($item) => $item->product_id)->toArray(),
                    ];

                    // Reduce the product (or variation type option) quantity
                    foreach ($order->orderItems as $orderItem) {
                        $options = $orderItem->variation_type_option_ids;
                        $product = $orderItem->product;

                        // If a variation type option of a product was purchased
                        if ($options) {
                            // Ensures that the IDs are sorted in the following format: [1,4] or [2,5] (NOT [4,1] or [5,2])
                            sort($options);

                            // Get the variation type option
                            $variationOption = $product->variations()->where('variation_type_option_ids', $options)->first();

                            // Check if the variation option exist and whose quantity is not null (or infinite stock)
                            if ($variationOption && $variationOption->quantity != null) {
                                // Reduce the variation option quantity
                                $variationOption->quantity -= $orderItem->quantity;
                                $variationOption->save();
                            }
                        
                        // Else, product has no variations and quantity is not unlimited
                        } elseif ($product->quantity != null) {
                            // Reduce the product quantity
                            $product->quantity -= $orderItem->quantity;
                            $product->save();
                        }
                    }
                }

                /**
                 * Delete the cart items based on the product IDs
                 * 
                 * Note: $order is obtained from the foreach loop above.
                 *       The foreach loop will ALWAYS at least loop once.
                 *       Hence, $order variable can be used here where the user ID for all those orders are the same, of course.
                 */
                CartItem::where('user_id', $order->user_id)
                        ->whereIn('product_id', $productsToBeDeletedFromCart)
                        ->where('checkout_later', false) // Exclude the cart items that is to be checkout later 
                        ->delete();
                break;
            
            default:
                echo 'Received unknown event type' . $event->type;
                break;
        }

        return response('', 200);
    }
}
