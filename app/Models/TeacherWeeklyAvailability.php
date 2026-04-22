<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherWeeklyAvailability extends Model
{
    use HasFactory;
    protected $fillable = [
        'teacher_profile_id',
        'town_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_minutes',
        'is_active',
    ];
}
