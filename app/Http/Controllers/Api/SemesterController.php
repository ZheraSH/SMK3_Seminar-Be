<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Semester;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::all();
        return response()->json($semesters);
    }

    public function active()
    {
        $semester = Semester::where('active', true)->first();
        return response()->json($semester);
    }

    public function cronStatus()
    {
        $activeSemester = Semester::where('active', true)->first();

        $status = $activeSemester 
            ? "Semester aktif: " . $activeSemester->name 
            : "Tidak ada semester aktif";

        return response()->json(['status' => $status]);
    }
}
