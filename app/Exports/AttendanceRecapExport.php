<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class AttendanceRecapExport implements FromCollection, WithTitle
{
    protected array $recapData;

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    /**
     * Convert recap data to collection for export
     */
    public function collection(): Collection
    {
        $rows = [];

        // Header information
        $rows[] = ['REKAP ABSENSI SISWA'];
        $rows[] = [''];
        $rows[] = ['Tanggal', $this->recapData['date'] . ' (' . $this->recapData['day_name'] . ')'];
        $rows[] = ['Kelas', $this->recapData['classroom']['name']];
        $rows[] = ['Tahun Ajaran', $this->recapData['tahun_ajaran']];
        $rows[] = ['Total Siswa', $this->recapData['total_students']];
        $rows[] = [''];

        // Attendance summary
        $rows[] = ['RINGKASAN KEHADIRAN'];
        $summary = $this->recapData['attendance_summary'];
        $rows[] = ['Hadir', $summary['present']];
        $rows[] = ['Terlambat', $summary['late']];
        $rows[] = ['Sakit', $summary['sick']];
        $rows[] = ['Izin', $summary['permission']];
        $rows[] = ['Alpha', $summary['alpha']];
        $rows[] = [''];

        // Student attendance list header
        $rows[] = ['No', 'NISN', 'Nama Siswa', 'Status Kehadiran'];

        // Student attendance data
        $no = 1;
        foreach ($this->recapData['students'] as $student) {
            $rows[] = [
                $no++,
                $student['nisn'],
                $student['student_name'],
                $this->translateStatus($student['status'])
            ];
        }

        return collect($rows);
    }

    protected function translateStatus(string $status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'alpha' => 'Alpha',
            default => $status
        };
    }

    /**
     * Sheet title
     */
    public function title(): string
    {
        return 'Rekap Absensi ' . $this->recapData['date'];
    }
}
