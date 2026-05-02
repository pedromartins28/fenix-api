<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExamDashboardRequest;
use App\Models\Exam;
use App\Services\ExamDashboardService;
use Illuminate\Http\JsonResponse;

class ExamDashboardController extends Controller
{
    private readonly ExamDashboardService $examDashboardService;

    public function __construct(ExamDashboardService $examDashboardService)
    {
        $this->examDashboardService = $examDashboardService;
    }

    public function show(ExamDashboardRequest $request, Exam $exam): JsonResponse
    {
        return response()->json(
            $this->examDashboardService->getDashboard($exam, $request->validated())
        );
    }
}
