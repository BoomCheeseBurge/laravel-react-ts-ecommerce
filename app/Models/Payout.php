<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'vendor_id',
        'amount',
        'start_date',
        'end_date',
    ];
}
