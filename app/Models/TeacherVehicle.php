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
                $q->whereNull('starts_at')
                ->whereNull('ends_at')
                ->orWhere(function ($q2) use ($date) {
                    $q2->where('starts_at', '<=', $date)
                        ->where('ends_at', '>=', $date);
                });
            })
            ->first();
    }

}


