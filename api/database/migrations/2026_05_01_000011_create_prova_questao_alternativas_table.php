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
        Schema::create('prova_questao_alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prova_questao')
                ->constrained('prova_questoes')
                ->cascadeOnDelete();
            $table->text('descricao');
            $table->boolean('is_correta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prova_questao_alternativas');
    }
};
