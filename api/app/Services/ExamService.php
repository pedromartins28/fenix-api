<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamService
{
    public function create(array $data): Exam
    {
        $this->validateBusinessRules($data);

        return DB::transaction(function () use ($data) {
            $exam = Exam::create([
                'name' => $data['name'],
                'questions_count' => $data['questions_count'],
                'value' => $data['value'],
            ]);

            $this->syncQuestions($exam, $data['questions']);

            return $exam->load(['classGroups:id,code', 'questions.options']);
        });
    }

    public function update(Exam $exam, array $data): Exam
    {
        $this->ensureExamCanBeChanged($exam);
        $this->validateBusinessRules($data);

        return DB::transaction(function () use ($exam, $data) {
            $exam->update([
                'name' => $data['name'],
                'questions_count' => $data['questions_count'],
                'value' => $data['value'],
            ]);

            if ($this->questionsChanged($exam, $data['questions'])) {
                $exam->questions()
                    ->with('options')
                    ->get()
                    ->each(function ($question): void {
                        $question->options()->delete();
                        $question->delete();
                    });

                $this->syncQuestions($exam, $data['questions']);
            }

            return $exam->load(['classGroups:id,code', 'questions.options']);
        });
    }

    public function delete(Exam $exam): void
    {
        $this->ensureExamCanBeChanged($exam);

        DB::transaction(function () use ($exam): void {
            $exam->classGroups()->detach();
            $exam->delete();
        });
    }

    private function ensureExamCanBeChanged(Exam $exam): void
    {
        if (! $exam->attempts()->exists()) {
            return;
        }

        throw ValidationException::withMessages([
            'exam' => 'This test has already been attempted by students and cannot be changed.',
        ]);
    }

    private function validateBusinessRules(array $data): void
    {
        if ((int) $data['questions_count'] !== count($data['questions'])) {
            throw ValidationException::withMessages([
                'questions' => 'The number of questions must match questions_count.',
            ]);
        }

        foreach ($data['questions'] as $index => $question) {
            $correctOptions = array_filter(
                $question['options'],
                fn(array $option): bool => $option['is_correct']
            );

            if (count($correctOptions) !== 1) {
                throw ValidationException::withMessages([
                    "questions.$index.options" => 'Each question must have exactly one correct option.',
                ]);
            }
        }
    }

    private function syncQuestions(Exam $exam, array $questions): void
    {
        foreach ($questions as $questionData) {
            $question = $exam->questions()->create([
                'statement' => $questionData['statement'],
                'position' => $questionData['position'],
            ]);

            foreach ($questionData['options'] as $optionData) {
                $question->options()->create([
                    'description' => $optionData['description'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }
    }

    private function questionsChanged(Exam $exam, array $newQuestions): bool
    {
        $current = $exam->load('questions.options')
            ->questions
            ->map(fn($q) => [
                'statement' => $q->statement,
                'position' => $q->position,
                'options' => $q->options->map(fn($o) => [
                    'description' => $o->description,
                    'is_correct' => (bool) $o->is_correct,
                ])->values()->toArray(),
            ])
            ->values()
            ->toArray();

        return $current !== array_values($newQuestions);
    }
}
