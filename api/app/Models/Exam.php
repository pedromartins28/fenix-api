<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['questions_count', 'value'])]
class Exam extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
        ];
    }

    public function classGroups(): BelongsToMany
    {
        return $this->belongsToMany(ClassGroup::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'exam_attempts')
            ->withPivot(['started_at', 'finished_at', 'correct_answers_count', 'score']);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }
}
