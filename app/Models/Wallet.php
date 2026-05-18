<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'student_profile_id',
        'balance',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
