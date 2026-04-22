<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSession extends Model
{
    use HasFactory;
    /* Campos rellenables*/
    protected $fillable = [
        'student_profile_id',
        'teacher_profile_id',
        'town_id',
        'vehicle_id',
        'session_date',
        'start_time',
        'end_time',
        'slot_starts_at',
        'slot_ends_at',
        'status',
        'payment_status',
        'price',
        'booking_reference',
        'student_comments',
        'internal_notes',
        'cancelled_at',
    ];
    /* Relaciones */
    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /* -------------------------
       Relaciones PaymentIntent
    --------------------------*/

    // Opción A: múltiples intentos
    public function paymentIntents()
    {
        return $this->hasMany(PaymentIntent::class);
    }

    // Opción B: solo un intento principal (MVP)
    public function paymentIntent()
    {
        return $this->hasOne(PaymentIntent::class);
    }
}
