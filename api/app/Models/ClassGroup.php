<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code'])]
class ClassGroup extends Model
{
    public $timestamps = false;

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class);
    }
}
