<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use Illuminate\Http\JsonResponse;

class ClassGroupController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            ClassGroup::query()
                ->select(['id', 'code'])
                ->orderBy('code')
                ->get()
        );
    }
}
