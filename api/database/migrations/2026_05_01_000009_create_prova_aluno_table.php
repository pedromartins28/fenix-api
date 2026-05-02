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
        Schema::create('prova_aluno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_aluno')
                ->constrained('alunos')
                ->restrictOnDelete();
            $table->foreignId('id_prova')
                ->constrained('provas')
                ->restrictOnDelete();
            $table->date('data_realizacao')->nullable();
            $table->decimal('nota', 8, 2)->nullable();

            $table->unique(['id_aluno', 'id_prova']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prova_aluno');
    }
};
