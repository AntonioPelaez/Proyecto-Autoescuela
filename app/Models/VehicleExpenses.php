<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleExpenses extends Model
{
    protected $table = 'vehicle_expenses';

    protected $fillable = [
        'vehicle_id',
        'class_session_id',
        'expense_type_id',
        'date',
        'amount',
        'description'
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
    public function classSession(){
        return $this->belongsTo(ClassSession::class, 'class_sesion_id');
    }
    public function expenseTypes()
    {
        return $this->belongsTo(ExpenseTypes::class, 'expense_type_id');
    }
}
