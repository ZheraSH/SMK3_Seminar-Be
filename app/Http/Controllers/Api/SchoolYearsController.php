<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Throwable;

class SchoolYearsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $data = SchoolYear::latest()->paginate(12);

            return ResponseHelper::success(
                $data,
                'Daftar tahun ajaran berhasil diambil'
            );

        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:school_years,name'
            ]);

            $data = SchoolYear::create([
                'name' => $request->name,
                'active' => false
            ]);

            return ResponseHelper::success(
                $data,
                'Tahun ajaran berhasil ditambahkan',
                201
            );

        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $year = SchoolYear::findOrFail($id);
            $year->delete();

            return ResponseHelper::success(null, 'Tahun ajaran berhasil dihapus');

        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function activate($id)
    {
        try {
            SchoolYear::where('active', true)->update(['active' => false]);

            $year = SchoolYear::findOrFail($id);
            $year->update(['active' => true]);

            return ResponseHelper::success(
                $year,
                'Tahun ajaran berhasil diaktifkan'
            );

        } catch (Throwable $th) {
            return ResponseHelper::error(500, $th->getMessage());
        }
    }

    public function active()
    {
        $active = SchoolYear::where('active', true)->first();

        return ResponseHelper::success(
            $active,
            'Tahun ajaran aktif berhasil diambil'
        );
    }
}
