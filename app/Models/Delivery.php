<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'order_id',
        'date',
        'status',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'instructions',
    ];

    // ----------------------------------------------------------------
    /**
     * 
     *   ______ _______        _______ _______ _____  _____  __   _
     *  |_____/ |______ |      |_____|    |      |   |     | | \  |
     *  |    \_ |______ |_____ |     |    |    __|__ |_____| |  \_|
     *
     */

    /**
     * Get the orderItem that owns the Address
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
