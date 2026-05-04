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
    private readonly ExamDashboardService $examDashboardService;

    public function __construct(ExamDashboardService $examDashboardService)
    {
        $this->examDashboardService = $examDashboardService;
    }

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
                    'started_at' => $attempt?->started_at,
                    'finished_at' => $attempt?->finished_at,
                    'correct_answers_count' => $attempt?->correct_answers_count,
                    'score' => $attempt?->score,
                ];
            });
    }

    public function showAvailableExam(Student $student, Exam $exam): array
    {
        $this->ensureExamIsAvailableForStudent($student, $exam);

        $attempt = $student->examAttempts()
            ->where('exam_id', $exam->id)
            ->first();

        return [
            ...$this->formatExamSummary($exam),
            'started_at' => $attempt?->started_at,
            'finished_at' => $attempt?->finished_at,
            'correct_answers_count' => $attempt?->correct_answers_count,
            'score' => $attempt?->score,
        ];
    }

    public function startAttempt(Student $student, Exam $exam): array
    {
        $this->ensureExamIsAvailableForStudent($student, $exam);

        if ($student->examAttempts()->where('exam_id', $exam->id)->exists()) {
            throw ValidationException::withMessages([
                'exam_id' => 'Este aluno já iniciou esta prova.',
            ]);
        }

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'started_at' => now(),
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
                'exam_id' => 'O aluno precisa iniciar a tentativa antes de visualizar as questões da prova.',
            ]);
        }

        $attempt->load('student');
        $exam->load('questions.options');

        return $this->formatAttemptForStudent($attempt, $exam);
    }

    public function submitAnswers(ExamAttempt $examAttempt, array $answers): ExamAttempt
    {
        if ($examAttempt->finished_at !== null || $examAttempt->answers()->exists()) {
            throw ValidationException::withMessages([
                'answers' => 'As respostas desta tentativa já foram enviadas.',
            ]);
        }

        $examAttempt->load('exam.questions');
        $questions = $examAttempt->exam->questions;

        if ($questions->count() !== count($answers)) {
            throw ValidationException::withMessages([
                'answers' => 'A quantidade de respostas deve ser igual à quantidade de questões da prova.',
            ]);
        }

        $questionsById = $questions->keyBy('id');
        $correctAnswers = 0;

        return DB::transaction(function () use ($examAttempt, $answers, $questionsById, &$correctAnswers) {
            foreach ($answers as $answer) {
                $question = $questionsById->get($answer['exam_question_id']);

                if ($question === null) {
                    throw ValidationException::withMessages([
                        'answers' => 'Todas as questões respondidas devem pertencer a esta prova.',
                    ]);
                }

                $option = ExamQuestionOption::query()
                    ->whereKey($answer['exam_question_option_id'])
                    ->where('exam_question_id', $question->id)
                    ->first();

                if ($option === null) {
                    throw ValidationException::withMessages([
                        'answers' => 'Cada alternativa selecionada deve pertencer à sua questão.',
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
            $examAttempt->update([
                'finished_at' => now(),
                'correct_answers_count' => $correctAnswers,
                'score' => round($score, 2),
            ]);

            $this->examDashboardService->forgetDashboardCache($examAttempt->exam_id);

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
                'exam_id' => 'Esta prova não está disponível para a turma do aluno.',
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
            'started_at' => $attempt->started_at,
            'finished_at' => $attempt->finished_at,
            'correct_answers_count' => $attempt->correct_answers_count,
            'score' => $attempt->score,
            'student' => $attempt->student,
            'exam' => $this->formatExamForStudent($exam),
        ];
    }

    private function formatExamSummary(Exam $exam): array
    {
        return [
            'id' => $exam->id,
            'name' => $exam->name,
            'questions_count' => $exam->questions_count,
            'value' => $exam->value,
        ];
    }
}
