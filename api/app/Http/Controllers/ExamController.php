<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamRequest;
use App\Models\Exam;
use App\Services\ExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ExamController extends Controller
{
    private readonly ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function index(): JsonResponse
    {
        return response()->json(
            Exam::with('classGroups:id,code')->get()
        );
    }

    public function store(ExamRequest $request): JsonResponse
    {
        $exam = $this->examService->create($request->validated());

        return response()->json($exam);
    }

    public function show(Exam $exam): JsonResponse
    {
        return response()->json(
            $exam->load(['classGroups:id,code', 'questions.options'])
        );
    }

    public function update(ExamRequest $request, Exam $exam): JsonResponse
    {
        $exam = $this->examService->update($exam, $request->validated());

        return response()->json($exam);
    }

    public function destroy(Exam $exam): Response
    {
        $exam->delete();

        return response()->noContent();
    }
}
