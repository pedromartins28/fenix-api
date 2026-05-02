<?php

use App\Http\Controllers\ClassGroupExamController;
use App\Http\Controllers\ExamDashboardController;
use App\Http\Controllers\ExamExecutionController;
use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::get('exams/{exam}/dashboard', [ExamDashboardController::class, 'show']);
Route::apiResource('exams', ExamController::class);

Route::get('class-groups/{classGroup}/exams', [ClassGroupExamController::class, 'index']);
Route::post('class-groups/{classGroup}/exams', [ClassGroupExamController::class, 'store']);
Route::delete('class-groups/{classGroup}/exams/{exam}', [ClassGroupExamController::class, 'destroy']);

Route::get('students/{student}/exams', [ExamExecutionController::class, 'index']);
Route::get('students/{student}/exams/{exam}', [ExamExecutionController::class, 'show']);
Route::get('students/{student}/exams/{exam}/attempt', [ExamExecutionController::class, 'showAttempt']);
Route::post('students/{student}/exams/{exam}/attempt', [ExamExecutionController::class, 'startAttempt']);
Route::post('exam-attempts/{examAttempt}/answers', [ExamExecutionController::class, 'submitAnswers']);
