<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherVehicle extends Model
{
   use HasFactory;
   protected $fillable = [
       'teacher_profile_id',
       'vehicle_id',
       'starts_at',
       'ends_at',
       'is_primary',
   ];

public static function getVehicleForDate($teacherId, $date)
{
    return self::where('teacher_profile_id', $teacherId)
        ->where(function ($q) use ($date) {
            $q->where(function ($q2) {
                // Vehículo siempre válido (sin rango)
                $q2->whereNull('starts_at')
                   ->whereNull('ends_at');
            })
            ->orWhere(function ($q2) use ($date) {
                // Rango con inicio y fin
                $q2->where('starts_at', '<=', $date)
                   ->where('ends_at', '>=', $date);
            })
            ->orWhere(function ($q2) use ($date) {
                // Rango abierto por arriba: starts_at <= date y ends_at NULL
                $q2->where('starts_at', '<=', $date)
                   ->whereNull('ends_at');
            });
        })
        ->orderByDesc('is_primary')
        ->first();
}

}


