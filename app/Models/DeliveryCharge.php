<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCharge extends Model
{
    protected $fillable = [
        'charge',
        'min_threshold',
        'max_threshold',
    ];
}
