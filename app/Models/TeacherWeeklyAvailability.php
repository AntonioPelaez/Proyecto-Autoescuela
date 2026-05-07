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
        'starts_time',
        'end_time',
        'slot_minutes',
        'is_active',
    ];

    /**
     * Relación con el perfil del profesor
     */
    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    /**
     * Relación con el pueblo
     */
    public function town()
    {
        return $this->belongsTo(Town::class, 'town_id');
    }
}
