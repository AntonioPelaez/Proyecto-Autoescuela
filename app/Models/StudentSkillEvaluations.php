<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSkillEvaluations extends Model
{
    protected $fillable = [
        'student_profile_id',
        'class_session_id',
        'teacher_profile_id',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function teacherProfile()
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function skillEvaluations()
    {
        return $this->hasMany(StudentSkillsEvaluations::class, 'student_skill_id');
    }
}

