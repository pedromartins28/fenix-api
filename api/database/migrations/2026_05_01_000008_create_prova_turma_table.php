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
        Schema::create('prova_turma', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prova')
                ->constrained('provas')
                ->restrictOnDelete();
            $table->foreignId('id_turma')
                ->constrained('turmas')
                ->restrictOnDelete();

            $table->unique(['id_prova', 'id_turma']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prova_turma');
    }
};
