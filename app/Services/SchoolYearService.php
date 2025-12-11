<?php

namespace App\Services;

use App\Contracts\Repositories\SchoolYearRepository; 
use Illuminate\Support\Facades\DB;

class SchoolYearService
{
    private SchoolYearRepository $schoolYearRepository;

    public function __construct(SchoolYearRepository $schoolYearRepository)
    {
        $this->schoolYearRepository = $schoolYearRepository;
    }

    public function paginate($request)
    {
        return $this->schoolYearRepository->paginate($request);
    }

    public function storeAuto()
    {
        return DB::transaction(function () {

            $this->schoolYearRepository->unsetAll();

            $latest = $this->schoolYearRepository->latest();

            if (!$latest) {
                $start = date('Y');
                $end = $start + 1;
            } else {
                [$start, $end] = explode('/', $latest->name);
                $start = (int)$start + 1;
                $end = $start + 1;
            }

            return $this->schoolYearRepository->store([
                'name' => "{$start}/{$end}",
                'active' => true
            ]);
        });
    }

    public function delete($id)
    {
        $year = $this->schoolYearRepository->show($id);

        if ($year->active) {
            throw new \Exception('Tidak dapat menghapus tahun ajaran aktif', 422);
        }

        return $this->schoolYearRepository->delete($id);
    }

    public function activate($id)
    {
        return DB::transaction(function () use ($id) {
            $this->schoolYearRepository->unsetAll();
            return $this->schoolYearRepository->setActive($id);
        });
    }

    public function active()
    {
        return $this->schoolYearRepository->active();
    }
}
