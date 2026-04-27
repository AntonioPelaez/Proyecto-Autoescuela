<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAvailabilityException extends Model
{
    protected $fillable = [
        'teacher_profile_id',
        'town_id',
        'exception_date',
        'starts_time',
        'end_time',
        'type',
        'reason',
    ];

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
