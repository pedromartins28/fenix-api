<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestionOption;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamExecutionService
{
    public function listAvailableExams(Student $student)
    {
        $attemptsByExamId = $student->examAttempts()
            ->get()
            ->keyBy('exam_id');

        return $student->classGroup
            ->exams()
            ->get()
            ->map(function (Exam $exam) use ($attemptsByExamId): array {
                $attempt = $attemptsByExamId->get($exam->id);

                return [
                    ...$this->formatExamSummary($exam),
                    'score' => $attempt?->score,
                ];
            });
    }

    public function showAvailableExam(Student $student, Exam $exam): array
    {
        $this->ensureExamIsAvailableForStudent($student, $exam);

        return $this->formatExamSummary($exam);
    }

    public function startAttempt(Student $student, Exam $exam): array
    {
        $this->ensureExamIsAvailableForStudent($student, $exam);

        if ($student->examAttempts()->where('exam_id', $exam->id)->exists()) {
            throw ValidationException::withMessages([
                'exam_id' => 'This student has already started this exam.',
            ]);
        }

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'taken_at' => now()->toDateString(),
        ])->load(['student']);

        $exam->load('questions.options');

        return $this->formatAttemptForStudent($attempt, $exam);
    }

    public function showAttempt(Student $student, Exam $exam): array
    {
        $this->ensureExamIsAvailableForStudent($student, $exam);

        $attempt = $student->examAttempts()
            ->where('exam_id', $exam->id)
            ->first();

        if ($attempt === null) {
            throw ValidationException::withMessages([
                'exam_id' => 'This student must start an attempt before seeing the exam questions.',
            ]);
        }

        $attempt->load('student');
        $exam->load('questions.options');

        return $this->formatAttemptForStudent($attempt, $exam);
    }

    public function submitAnswers(ExamAttempt $examAttempt, array $answers): ExamAttempt
    {
        if ($examAttempt->answers()->exists()) {
            throw ValidationException::withMessages([
                'answers' => 'Answers have already been submitted for this attempt.',
            ]);
        }

        $examAttempt->load('exam.questions');
        $questions = $examAttempt->exam->questions;

        if ($questions->count() !== count($answers)) {
            throw ValidationException::withMessages([
                'answers' => 'The number of answers must match the number of exam questions.',
            ]);
        }

        $questionsById = $questions->keyBy('id');
        $correctAnswers = 0;

        return DB::transaction(function () use ($examAttempt, $answers, $questionsById, &$correctAnswers) {
            foreach ($answers as $answer) {
                $question = $questionsById->get($answer['exam_question_id']);

                if ($question === null) {
                    throw ValidationException::withMessages([
                        'answers' => 'All answered questions must belong to this exam.',
                    ]);
                }

                $option = ExamQuestionOption::query()
                    ->whereKey($answer['exam_question_option_id'])
                    ->where('exam_question_id', $question->id)
                    ->first();

                if ($option === null) {
                    throw ValidationException::withMessages([
                        'answers' => 'Each selected option must belong to its question.',
                    ]);
                }

                $examAttempt->answers()->create([
                    'exam_question_id' => $question->id,
                    'exam_question_option_id' => $option->id,
                ]);

                if ($option->is_correct) {
                    $correctAnswers++;
                }
            }

            $score = ($correctAnswers / $questionsById->count()) * (float) $examAttempt->exam->value;
            $examAttempt->update(['score' => round($score, 2)]);

            return $examAttempt->load(['exam', 'answers.examQuestionOption']);
        });
    }

    private function ensureExamIsAvailableForStudent(Student $student, Exam $exam): void
    {
        $isAvailable = $student->classGroup
            ->exams()
            ->whereKey($exam->id)
            ->exists();

        if (! $isAvailable) {
            throw ValidationException::withMessages([
                'exam_id' => 'This exam is not available for the student class group.',
            ]);
        }
    }

    private function formatExamForStudent(Exam $exam): array
    {
        return [
            ...$this->formatExamSummary($exam),
            'questions' => $exam->questions->map(fn($question): array => [
                'id' => $question->id,
                'statement' => $question->statement,
                'position' => $question->position,
                'options' => $question->options->map(fn($option): array => [
                    'id' => $option->id,
                    'description' => $option->description,
                ]),
            ]),
        ];
    }

    private function formatAttemptForStudent(ExamAttempt $attempt, Exam $exam): array
    {
        return [
            'id' => $attempt->id,
            'exam_id' => $attempt->exam_id,
            'student_id' => $attempt->student_id,
            'taken_at' => $attempt->taken_at,
            'score' => $attempt->score,
            'student' => $attempt->student,
            'exam' => $this->formatExamForStudent($exam),
        ];
    }

    private function formatExamSummary(Exam $exam): array
    {
        return [
            'id' => $exam->id,
            'questions_count' => $exam->questions_count,
            'value' => $exam->value,
        ];
    }
}
