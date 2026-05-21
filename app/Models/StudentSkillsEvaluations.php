<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkillsEvaluations extends Model
{
    protected $fillable = [
        'student_skill_id',
        'driving_skill_id',
        'score',
        'ready_for_exam',
        'notes'
    ];

    public function evaluation()
    {
        return $this->belongsTo(StudentSkillEvaluations::class, 'student_skill_id');
    }

    public function drivingSkill()
    {
        return $this->belongsTo(DrivingSkills::class, 'driving_skill_id');
    }
}

