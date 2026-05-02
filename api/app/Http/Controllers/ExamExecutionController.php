<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitExamAnswersRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Services\ExamExecutionService;
use Illuminate\Http\JsonResponse;

class ExamExecutionController extends Controller
{
    private readonly ExamExecutionService $examExecutionService;

    public function __construct(ExamExecutionService $examExecutionService)
    {
        $this->examExecutionService = $examExecutionService;
    }

    public function index(Student $student): JsonResponse
    {
        return response()->json(
            $this->examExecutionService->listAvailableExams($student)
        );
    }

    public function show(Student $student, Exam $exam): JsonResponse
    {
        return response()->json(
            $this->examExecutionService->showAvailableExam($student, $exam)
        );
    }

    public function startAttempt(Student $student, Exam $exam): JsonResponse
    {
        $attempt = $this->examExecutionService->startAttempt($student, $exam);

        return response()->json($attempt);
    }

    public function showAttempt(Student $student, Exam $exam): JsonResponse
    {
        $attempt = $this->examExecutionService->showAttempt($student, $exam);

        return response()->json($attempt);
    }

    public function submitAnswers(SubmitExamAnswersRequest $request, ExamAttempt $examAttempt): JsonResponse
    {
        $attempt = $this->examExecutionService->submitAnswers($examAttempt, $request->validated('answers'));

        return response()->json($attempt);
    }
}
