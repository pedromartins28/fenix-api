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
        Schema::create('prova_questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prova')
                ->constrained('provas')
                ->cascadeOnDelete();
            $table->text('enunciado');
            $table->unsignedInteger('ordem');

            $table->unique(['id_prova', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prova_questoes');
    }
};
