<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_number',
        'type',
        'trips',
        'gross_revenue',
        'travel_allowance',
        'profit',
        'operating_cost',
        'availability',
        'period',
    ];
}
