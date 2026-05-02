<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['num_questoes', 'valor'])]
class Prova extends Model
{
    protected $table = 'provas';

    public $timestamps = false;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'prova_turma', 'id_prova', 'id_turma');
    }

    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'prova_aluno', 'id_prova', 'id_aluno')
            ->withPivot(['data_realizacao', 'nota']);
    }
}
