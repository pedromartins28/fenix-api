<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassGroupExamRequest;
use App\Models\ClassGroup;
use App\Models\Exam;
use App\Services\ClassGroupExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ClassGroupExamController extends Controller
{

    private readonly ClassGroupExamService $classGroupExamService;

    public function __construct(ClassGroupExamService $classGroupExamService)
    {
        $this->classGroupExamService = $classGroupExamService;
    }

    public function index(ClassGroup $classGroup): JsonResponse
    {
        return response()->json(
            $this->classGroupExamService->listExams($classGroup)
        );
    }

    public function store(ClassGroupExamRequest $request, ClassGroup $classGroup): JsonResponse
    {
        $exam = $this->classGroupExamService->attachExam($classGroup, $request->integer('exam_id'));

        return response()->json($exam);
    }

    public function destroy(ClassGroup $classGroup, Exam $exam): Response
    {
        $this->classGroupExamService->detachExam($classGroup, $exam);

        return response()->noContent();
    }
}
