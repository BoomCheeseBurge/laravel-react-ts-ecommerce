<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone_number',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
    ];
}
