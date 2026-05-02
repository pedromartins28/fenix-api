<?php

use App\Http\Controllers\ClassGroupExamController;
use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('exams', ExamController::class);

Route::get('class-groups/{classGroup}/exams', [ClassGroupExamController::class, 'index']);
Route::post('class-groups/{classGroup}/exams', [ClassGroupExamController::class, 'store']);
Route::delete('class-groups/{classGroup}/exams/{exam}', [ClassGroupExamController::class, 'destroy']);
