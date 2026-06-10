<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelLogs extends Model
{
    protected $table = 'fuel_logs';

    protected $fillable = [
        'vehicle_id',
        'date',
        'liters',
        'amount',
        'kilometers',
        'notes'
    ];
    public function vehicle()
{
    return $this->belongsTo(Vehicle::class, 'vehicle_id');
}
}
