<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamStudents extends Model
{
    protected $table = 'exam_students';

    protected $fillable = [
        'exam_call_id',
        'student_id',
        'teacher_id',
        'vehicle_id',
        'exam_result_status_id',
        'result_notes',
        'student_confirmed',
        'student_confirmed_at',
    ];

    public function examCall()
    {
        return $this->belongsTo(ExamCalls::class, 'exam_call_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function examResultStatus()
    {
        return $this->belongsTo(ExamResultStatus::class, 'exam_result_status_id');
    }
}
