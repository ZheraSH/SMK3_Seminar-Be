<?php

namespace App\Services;

use App\Contracts\Interfaces\AttendanceMonitoringInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceMonitoringService
{
    public function __construct(
        private AttendanceMonitoringInterface $attendanceMonitoringRepository
    ) {}

    /**
     * Get monitoring list (with PPLG validation)
     */
    public function getMonitoringData(array $filters)
    {
        if (!empty($filters['major']) && $filters['major'] !== 'PPLG') {

            $perPage = $filters['limit'] ?? 15;

            return new LengthAwarePaginator(
                [],
                0,
                $perPage,
                1
            );
        }

        return $this->attendanceMonitoringRepository->getMonitoringData($filters);
    }

    /**
     * Recap should always reflect FILTER + LIST RESULT
     */
    public function getRecap(array $filters): array
    {
        $list = $this->getMonitoringData($filters);

        if ($list->total() === 0) {
            return [
                'jumlah_siswa_hadir_hari_ini' => 0,
                'jumlah_siswa_sakit_hari_ini' => 0,
                'jumlah_siswa_izin_hari_ini'  => 0,
                'jumlah_siswa_alpha_hari_ini' => 0,
                'note' => 'Data yang dicari tidak memiliki rekam kehadiran.'
            ];
        }

        $first = $list->items()[0];
        $major = $first->major_code ?? null;

        if ($major !== 'PPLG') {
            return [
                'jumlah_siswa_hadir_hari_ini' => 0,
                'jumlah_siswa_sakit_hari_ini' => 0,
                'jumlah_siswa_izin_hari_ini'  => 0,
                'jumlah_siswa_alpha_hari_ini' => 0,
                'note' => 'Recap tidak tersedia untuk jurusan ini.'
            ];
        }

        return $this->attendanceMonitoringRepository->getRecap($filters);
    }

    /**
     * Sync data (simply pass-through)
     */
    public function syncLatestData(): bool
    {
        return $this->attendanceMonitoringRepository->syncLatestData();
    }
}
