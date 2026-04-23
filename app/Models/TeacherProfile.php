<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherProfile extends Model
{
    use HasFactory;

    /* Campos rellenables*/
    protected $fillable = [
        'user_id',
        'dni',
        'license_number',
        'notes',
        'is_active_for_booking',
    ];

    /* Relaciones */
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function towns()
    {
        return $this->belongsToMany(Town::class, 'teacher_towns', 'teacher_profile_id', 'town_id');
    }

    public function vehicles(){
        return $this->belongsToMany(Vehicle::class, 'teacher_vehicles');
    }

    public function weeklyAvailabilities(){
        return $this->hasMany(TeacherWeeklyAvailability::class);
    }

    public function availabilities(){
        return $this->hasMany(TeacherAvailabilityException::class);
    }

    public function classSession(){
        return $this->hasMany(ClassSession::class);
    }
}
