<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'stripe_session_id',
        'user_id',
        'total_price',
        'status',
        'online_payment_commission',
        'website_commission',
        'vendor_subtotal',
        'payment_intent',
    ];

    /**
     * The "booted" method of the model.
     */
     protected static function booted(): void
     {
         static::deleting(function (Order $order) {
 
            $order->orderItems()->delete(); // Delete associated order items

            // Get the Stripe client
            $stripe = new \Stripe\StripeClient(config('app.stripe_secret_key'));

            // Expire the checkout session
            $stripe->checkout->sessions->expire(
            $order->stripe_session_id,
            []
            );
 
         });
     }

    // ---------------------------------------------------------------------
    /**
     * 
     *   _______ _______  _____   _____  _______
     *   |______ |       |     | |_____] |______
     *   ______| |_____  |_____| |       |______
     *
     */

    /**
     * Scope a query to only include products belonging to the vendor user.
     */
    public function scopeBelongsToVendor(Builder $query, int $id): Builder
    {
        return $query->where('vendor_user_id', $id);
    }

    // ----------------------------------------------------------------
    /**
     * 
     *   ______ _______        _______ _______ _____  _____  __   _
     *  |_____/ |______ |      |_____|    |      |   |     | | \  |
     *  |    \_ |______ |_____ |     |    |    __|__ |_____| |  \_|
     *
     */
    /**
     * Get all of the orderItems for the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the user that made the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the vendorUser that receives the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }

    /**
     * Get the vendor that is related to the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_user_id', 'user_id');
    }
}
