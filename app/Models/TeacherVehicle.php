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
}
