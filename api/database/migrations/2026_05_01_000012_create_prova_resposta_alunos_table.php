<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prova_resposta_alunos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prova_aluno')
                ->constrained('prova_aluno')
                ->restrictOnDelete();
            $table->foreignId('id_prova_questao')
                ->constrained('prova_questoes')
                ->restrictOnDelete();
            $table->foreignId('id_prova_questao_alternativa')
                ->constrained('prova_questao_alternativas')
                ->restrictOnDelete();

            $table->unique(['id_prova_aluno', 'id_prova_questao']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prova_resposta_alunos');
    }
};
