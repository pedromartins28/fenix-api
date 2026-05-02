<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_question_id', 'description', 'is_correct'])]
class ExamQuestionOption extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function examQuestion(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class);
    }
}
