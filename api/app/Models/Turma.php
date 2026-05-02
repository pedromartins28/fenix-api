<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['codigo'])]
class Turma extends Model
{
    protected $table = 'turmas';

    public $timestamps = false;

    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'id_turma');
    }

    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(Professor::class, 'professor_turma', 'id_turma', 'id_professor');
    }

    public function provas(): BelongsToMany
    {
        return $this->belongsToMany(Prova::class, 'prova_turma', 'id_turma', 'id_prova');
    }
}
