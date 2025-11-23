<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Interfaces\SchoolYearInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Throwable;

class SchoolYearsController extends Controller
{
    protected $repo;

    public function __construct(SchoolYearInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        return ResponseHelper::success(
            $this->repo->paginate(),
            'Daftar tahun ajaran berhasil diambil'
        );
    }

    public function store()
    {
        DB::beginTransaction();
        try {
            
            $this->repo->storeAuto();

            DB::commit();
            return ResponseHelper::success(null, 'Tahun ajaran berhasil ditambahkan');

        } catch (Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(null, 'Gagal menambah tahun ajaran: ' . $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $year = $this->repo->show($id);

            if ($year->active) {
                return ResponseHelper::error('Tidak dapat menghapus tahun ajaran aktif', 422);
            }

            $this->repo->delete($id);

            return ResponseHelper::success(null, 'Tahun ajaran berhasil dihapus');

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function activate($id)
    {
        try {
            $this->repo->unsetAll();
            $this->repo->setActive($id);

            DB::commit();
            return ResponseHelper::success(null, 'Tahun ajaran berhasil diaktifkan');

        } catch (Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }

    public function active()
    {
        try {
            $active = $this->repo->active();

            if (!$active) {
                return ResponseHelper::error('Tidak ada tahun ajaran aktif', 404);
            }

            return ResponseHelper::success($active, 'Tahun ajaran aktif berhasil diambil');

        } catch (Throwable $th) {
            return ResponseHelper::error($th->getMessage(), 500);
        }
    }
}