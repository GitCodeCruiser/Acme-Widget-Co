<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpendThresholdOffer extends Model
{
    protected $fillable = [
        'product_id',
        'every_nth',
        'discount_type',
        'discount_amount',
        'offer_id',
    ];
}
