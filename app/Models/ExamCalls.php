<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCalls extends Model
{
    protected $table = 'exam_calls';

    protected $fillable = [
        'town_id',
        'exam_date',
        'start_time',
        'exam_call_status_id',
        'notes',
        'max_students',
    ];

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function examCallStatus()
    {
        return $this->belongsTo(ExamCallStatus::class, 'exam_call_status_id');
    }

    public function examStudents()
    {
        return $this->hasMany(ExamStudents::class, 'exam_call_id');
    }
}
