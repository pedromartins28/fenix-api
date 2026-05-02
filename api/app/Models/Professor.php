<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['id_user', 'nome'])]
class Professor extends Model
{
    protected $table = 'professores';

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'professor_turma', 'id_professor', 'id_turma');
    }
}
