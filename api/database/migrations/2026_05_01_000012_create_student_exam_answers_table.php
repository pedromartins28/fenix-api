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
        Schema::create('student_exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_student_id')
                ->constrained('exam_student')
                ->restrictOnDelete();
            $table->foreignId('exam_question_id')
                ->constrained('exam_questions')
                ->restrictOnDelete();
            $table->foreignId('exam_question_option_id')
                ->constrained('exam_question_options')
                ->restrictOnDelete();

            $table->unique(['exam_student_id', 'exam_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_exam_answers');
    }
};
