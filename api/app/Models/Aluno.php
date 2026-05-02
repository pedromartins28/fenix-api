<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['id_user', 'nome', 'id_turma'])]
class Aluno extends Model
{
    protected $table = 'alunos';

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'id_turma');
    }

    public function provas(): BelongsToMany
    {
        return $this->belongsToMany(Prova::class, 'prova_aluno', 'id_aluno', 'id_prova')
            ->withPivot(['data_realizacao', 'nota']);
    }
}
