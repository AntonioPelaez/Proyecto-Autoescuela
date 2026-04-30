<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dni',
        'birth_date',
        'pickup_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'student_profile_id');
    }
}