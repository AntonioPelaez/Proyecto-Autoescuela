<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id',
        'dni',
        'license_number',
        'notes',
        'is_active_for_booking',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function towns()
    {
        return $this->belongsToMany(Town::class, 'teacher_towns', 'teacher_profile_id', 'town_id');
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'teacher_vehicles', 'teacher_profile_id', 'vehicle_id')
            ->withPivot(['starts_at', 'ends_at', 'is_primary', 'created_at', 'updated_at']);
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'teacher_profile_id');
    }
}
