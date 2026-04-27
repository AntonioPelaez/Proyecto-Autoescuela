<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherTown extends Model
{
    use HasFactory;
    protected $fillable = [
        'teacher_profile_id',
        'town_id',
    ];

    public function teacherProfile()
{
    return $this->belongsTo(\App\Models\TeacherProfile::class, 'teacher_profile_id');
}

}


