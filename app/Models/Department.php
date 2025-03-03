<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
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
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true);
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
     * Get all of the categories for the Department
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
}
