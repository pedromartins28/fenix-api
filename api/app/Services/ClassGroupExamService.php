<?php

namespace App\Services;

use App\Models\ClassGroup;
use App\Models\Exam;
use Illuminate\Validation\ValidationException;

class ClassGroupExamService
{
    public function listExams(ClassGroup $classGroup)
    {
        return $classGroup->exams()
            ->with('classGroups:id,code')
            ->get();
    }

    public function attachExam(ClassGroup $classGroup, int $examId): Exam
    {
        if ($classGroup->exams()->whereKey($examId)->exists()) {
            throw ValidationException::withMessages([
                'exam_id' => 'This exam is already linked to this class group.',
            ]);
        }

        $classGroup->exams()->syncWithoutDetaching([$examId]);

        return Exam::with(['classGroups:id,code'])->findOrFail($examId);
    }

    public function detachExam(ClassGroup $classGroup, Exam $exam): void
    {
        if ($exam->attempts()->whereHas('student', function ($query) use ($classGroup): void {
            $query->where('class_group_id', $classGroup->id);
        })->exists()) {
            throw ValidationException::withMessages([
                'exam_id' => 'This exam has already been attempted by students of this class.',
            ]);
        }

        $classGroup->exams()->detach($exam->id);
    }
}
