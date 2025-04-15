<?php

namespace App\Models;

use App\Enums\VendorStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;
    
    // Change the primary key
    protected $primaryKey = 'user_id';

    // ---------------------------------------------------------------------
    /**
     * 
     *   _______ _______  _____   _____  _______
     *   |______ |       |     | |_____] |______
     *   ______| |_____  |_____| |       |______
     *
     */

    /**
     * Scope a query to check whether the vendor user is eligible for payout.
     */
    public function scopeEligibleForPayout(Builder $query): Builder
    {
        return $query->where('status', VendorStatusEnum::Approved)
                    ->join('users', 'users.id', '=', 'vendors.user_id')
                    ->where('users.stripe_account_active', true);
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
     * Get the user that owns the Vendor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
