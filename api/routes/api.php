<?php

use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::apiResource('exams', ExamController::class);
