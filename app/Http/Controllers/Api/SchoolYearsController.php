<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SchoolYearResource;
use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Requests\StoreSchoolYearRequest;
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
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function store(StoreSchoolYearRequest $request)
    {
        try {

            SchoolYear::where('active', true)->update(['active' => false]);

            $data = SchoolYear::create([
                'name' => $request->name,
                'active' => true
            ]);

            return ResponseHelper::success(
                new SchoolYearResource($data),
                'Tahun ajaran berhasil ditambahkan dan diaktifkan',
                201
            );

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }   
    }

    public function destroy($id)
    {
        try {
            $year = SchoolYear::findOrFail($id);
            
            if ($year->active === true) {
                return ResponseHelper::error('Tidak dapat menghapus tahun ajaran aktif', 422);
            }
            
            $year->delete();

            return ResponseHelper::success(null, 'Tahun ajaran berhasil dihapus');

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function activate($id)
    {
        try {
    
            SchoolYear::where('active', true)->update(['active' => false]);

            $year = SchoolYear::findOrFail($id);
            $year->update(['active' => true]);

            return ResponseHelper::success(
                new SchoolYearResource($year),
                'Tahun ajaran berhasil diaktifkan'
            );

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function active()
    {
        try {
            $active = SchoolYear::where('active', true)->first();

            if (!$active) {
                return ResponseHelper::error('Tidak ada tahun ajaran aktif', 404);
            }

            return ResponseHelper::success(
                new SchoolYearResource($active),
                'Tahun ajaran aktif berhasil diambil'
            );

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }
}