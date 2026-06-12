<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleExpenses extends Model
{
    protected $table = 'vehicle_expenses';

    protected $fillable = [
        'vehicle_id',
        'amount',
        'description'
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
