<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    public function active(): JsonResponse
    {
        $semester = Semester::where('active', true)->first();

        return response()->json([
            'status' => true,
            'message' => 'Semester aktif saat ini',
            'data' => $semester ? [
                'id' => $semester->id,
                'name' => $semester->name,
            ] : null
        ]);
    }
}
