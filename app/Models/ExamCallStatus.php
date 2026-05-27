<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamCallStatus extends Model
{
    protected $table = 'exam_call_status';

    protected $fillable = [
        'name',
        'label',
    ];

    public function examCalls()
    {
        return $this->hasMany(ExamCalls::class, 'exam_call_status_id');
    }
    public function students()
    {
        return $this->hasMany(ExamStudents::class);
    }
}
