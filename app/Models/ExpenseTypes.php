<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseTypes extends Model
{
    protected $table = 'expense_types';

    protected $fillable = [
        'name'
    ];

    public function vehicleExpenses(){
        return $this->hasMany(VehicleExpenses::class, 'expense_type_id');
    }
}
