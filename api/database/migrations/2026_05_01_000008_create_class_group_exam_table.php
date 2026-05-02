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
        Schema::create('class_group_exam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')
                ->constrained('class_groups')
                ->restrictOnDelete();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->restrictOnDelete();

            $table->unique(['class_group_id', 'exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_group_exam');
    }
};
