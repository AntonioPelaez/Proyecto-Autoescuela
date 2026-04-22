<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    /* Campos rellenables*/
    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'is_active',
        'notes',
    ];

    /* Relaciones */
    public function teachers(){
        return $this->belongsToMany(TeacherProfile::class, 'teacher_vehicles');
    }
    public function sessionClass(){
        return $this->hasMany(ClassSession::class);
    }
}
