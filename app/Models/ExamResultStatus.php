<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResultStatus extends Model
{
    protected $table = 'exam_result_statuses';

    protected $fillable = [
        'name',
        'label',
    ];

    public function examStudents()
    {
        return $this->hasMany(ExamStudents::class, 'exam_result_status_id');
    }
}
