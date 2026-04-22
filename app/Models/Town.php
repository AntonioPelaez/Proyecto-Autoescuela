<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Town extends Model
{
    use HasFactory;

     /* Campos rellenables*/
    protected $fillable = [
        'name',
        'postal_code',
        'is_active',
    ];
     
    /* Relaciones */
    public function teachers(){
        return $this->belongsToMany(TeacherProfile::class, 'teacher_towns');
    }
    public function teacherAvailabilities(){
        return $this->hasMany(TeacherWeeklyAvailability::class);
    }
    public function sessionClass(){
        return $this->hasMany(ClassSession::class);
    }
}
