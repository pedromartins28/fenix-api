<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        return $this->belongsToMany(Student::class)
            ->withPivot(['taken_at', 'score']);
    }
}
