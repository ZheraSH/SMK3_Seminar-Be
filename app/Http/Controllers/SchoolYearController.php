<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    // Ambil semua data
    public function index()
    {
        return response()->json(SchoolYear::all(), 200);
    }

    // Tambah data baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:50',
            'active' => 'boolean',
        ]);

        $schoolYear = SchoolYear::create($validated);

        return response()->json($schoolYear, 201);
    }

    // Lihat detail
    public function show($id)
    {
        $schoolYear = SchoolYear::findOrFail($id);
        return response()->json($schoolYear, 200);
    }

    // Update
    public function update(Request $request, $id)
    {
        $schoolYear = SchoolYear::findOrFail($id);

        $validated = $request->validate([
            'school_year' => 'sometimes|required|string|max:50',
            'active' => 'boolean',
        ]);

        $schoolYear->update($validated);

        return response()->json($schoolYear, 200);
    }

    // Hapus
    public function destroy($id)
    {
        $schoolYear = SchoolYear::findOrFail($id);
        $schoolYear->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
