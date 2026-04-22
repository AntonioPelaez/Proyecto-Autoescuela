<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentIntent extends Model
{
    use HasFactory;
    protected $fillable = [
        'class_session_id',
        'provider',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'payload',
        'paid_at',
    ];
}
