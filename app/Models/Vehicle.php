<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'year',
        'is_active',
    ];

    public function teachers()
    {
        return $this->belongsToMany(TeacherProfile::class, 'teacher_vehicles', 'vehicle_id', 'teacher_profile_id')
            ->withPivot(['starts_at', 'ends_at', 'is_primary', 'created_at', 'updated_at']);
    }
    
    public function sessionClass(){
        return $this->hasMany(ClassSession::class);
    }
}
