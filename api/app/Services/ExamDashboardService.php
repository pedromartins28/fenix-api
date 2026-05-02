<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ExamDashboardService
{
    public function getDashboard(Exam $exam, array $filters): array
    {
        $this->ensureExamIsAvailableForClassGroup($exam, $filters['class_group_id'] ?? null);

        $attemptsQuery = $this->finishedAttemptsQuery($exam, $filters);
        $averageScore = (clone $attemptsQuery)->avg('score');
        $topAttempt = $this->topAttempt($attemptsQuery);
        $ranking = $this->ranking($attemptsQuery, (int) ($filters['per_page'] ?? 15));

        return [
            'exam' => [
                'id' => $exam->id,
                'questions_count' => $exam->questions_count,
                'value' => $exam->value,
            ],
            'filters' => [
                'class_group_id' => $filters['class_group_id'] ?? null,
            ],
            'average_score' => $averageScore !== null ? round((float) $averageScore, 2) : null,
            'top_score' => $topAttempt?->score,
            'top_attempt' => $topAttempt ? $this->formatAttempt($topAttempt) : null,
            'ranking' => $ranking,
        ];
    }

    private function finishedAttemptsQuery(Exam $exam, array $filters): Builder
    {
        return ExamAttempt::query()
            ->with(['student.classGroup'])
            ->where('exam_id', $exam->id)
            ->whereNotNull('finished_at')
            ->when($filters['class_group_id'] ?? null, function (Builder $query, int $classGroupId): void {
                $query->whereHas('student', function (Builder $studentQuery) use ($classGroupId): void {
                    $studentQuery->where('class_group_id', $classGroupId);
                });
            });
    }

    private function ensureExamIsAvailableForClassGroup(Exam $exam, mixed $classGroupId): void
    {
        if ($classGroupId === null) {
            return;
        }

        $isAvailable = $exam->classGroups()
            ->whereKey((int) $classGroupId)
            ->exists();

        if (! $isAvailable) {
            throw ValidationException::withMessages([
                'class_group_id' => 'This exam is not available for the selected class group.',
            ]);
        }
    }

    private function topAttempt(Builder $attemptsQuery): ?ExamAttempt
    {
        return (clone $attemptsQuery)
            ->orderByDesc('score')
            ->orderByDesc('correct_answers_count')
            ->orderBy('finished_at')
            ->first();
    }

    private function ranking(Builder $attemptsQuery, int $perPage)
    {
        return (clone $attemptsQuery)
            ->orderByDesc('score')
            ->orderByDesc('correct_answers_count')
            ->orderBy('finished_at')
            ->paginate($perPage)
            ->through(fn(ExamAttempt $attempt): array => $this->formatAttempt($attempt));
    }

    private function formatAttempt(ExamAttempt $attempt): array
    {
        return [
            'attempt_id' => $attempt->id,
            'student' => [
                'id' => $attempt->student->id,
                'name' => $attempt->student->name,
                'class_group' => [
                    'id' => $attempt->student->classGroup->id,
                    'code' => $attempt->student->classGroup->code,
                ],
            ],
            'correct_answers_count' => $attempt->correct_answers_count,
            'score' => $attempt->score,
            'started_at' => $attempt->started_at,
            'finished_at' => $attempt->finished_at,
        ];
    }
}
