<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_attempt_id', 'exam_question_id', 'exam_question_option_id'])]
class StudentExamAnswer extends Model
{
    public $timestamps = false;

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function examQuestionOption(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionOption::class);
    }
}
