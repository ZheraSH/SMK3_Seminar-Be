<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceGlobalInterface;
use App\Models\Attendance;
use App\Traits\PaginationTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceGlobalRepository extends BaseRepository implements AttendanceGlobalInterface
{
    use PaginationTrait;

    public function __construct(Attendance $attendance)
    {
        $this->model = $attendance;
    }

    public function get(): Collection
    {
        return $this->model
            ->with([
                'student.user',
                'student.classroomStudents.classroom.major'
            ])
            ->latest()
            ->get();
    }

    public function getPaginated(array $filters): LengthAwarePaginator
    {
        $items = $this->applyFilters($this->get(), $filters);

        return self::paginateCollection(
            $items,
            $filters['per_page'] ?? 15,
            $filters['page'] ?? null
        );
    }

    public function search(array $filters): LengthAwarePaginator
    {
        return $this->getPaginated($filters);
    }

    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * GLOBAL STATISTICS
     */
    public function getGlobalStats(array $filters): array
    {
        $items = $this->applyFilters($this->get(), $filters);

        $converted = $items->map(
            fn($item) => $this->mapStatus($item->status->value)
        );

        return [
            'present' => $converted->where(fn($v) => $v === 'present')->count(),
            'leave'   => $converted->where(fn($v) => $v === 'leave')->count(),
            'sick'    => $converted->where(fn($v) => $v === 'sick')->count(),
            'alpha'   => $converted->where(fn($v) => $v === 'alpha')->count(),
        ];
    }

    /**
     * MONTHLY TREND
     */
    public function getMonthlyTrend(array $filters): array
    {
        $year   = $filters['year'] ?? date('Y');
        $months = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Aug',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
        ];

        $items = $this->applyFilters($this->get(), $filters);

        $trend = [];

        foreach ($months as $num => $label) {
            $monthItems = $items->filter(
                fn($i) => $i->date->year == $year && $i->date->month == $num
            );

            $total   = $monthItems->count();
            $present = $monthItems->where(fn($m) => $m->status->value === 'hadir')->count();

            $trend[$label] = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        }

        return $trend;
    }


    /*
    |--------------------------------------------------------------------------
    | PRIVATE METHODS (SOLID)
    |--------------------------------------------------------------------------
    */

    private function mapStatus(string $status): string
    {
        return [
            'hadir'     => 'present',
            'terlambat' => 'present',
            'sakit'     => 'sick',
            'izin'      => 'leave',
            'alpha'     => 'alpha',
        ][strtolower($status)] ?? 'alpha';
    }

    private function applyFilters(Collection $items, array $filters): Collection
    {
        return $items
            ->filter(fn($i) => $this->filterByClassroom($i, $filters))
            ->filter(fn($i) => $this->filterByMajor($i, $filters))
            ->filter(fn($i) => $this->filterByMonth($i, $filters))
            ->filter(fn($i) => $this->filterByYear($i, $filters))
            ->filter(fn($i) => $this->filterByDateRange($i, $filters))
            ->values();
    }

    private function filterByClassroom($item, array $filters): bool
    {
        if (empty($filters['classroom_id'])) return true;
        if (strlen($filters['classroom_id']) < 8) return true;

        return $item->student->classroomStudents->last()?->classroom_id === $filters['classroom_id'];
    }

    private function filterByMajor($item, array $filters): bool
    {
        return empty($filters['major_code'])
            ? true
            : $item->student->classroomStudents->last()?->classroom?->major?->code === $filters['major_code'];
    }

    private function filterByMonth($item, array $filters): bool
    {
        return empty($filters['month'])
            ? true
            : $item->date->month == (int)$filters['month'];
    }

    private function filterByYear($item, array $filters): bool
    {
        return empty($filters['year'])
            ? true
            : $item->date->year == (int)$filters['year'];
    }

    private function filterByDateRange($item, array $filters): bool
    {
        return empty($filters['start_date']) || empty($filters['end_date'])
            ? true
            : $item->date->between($filters['start_date'], $filters['end_date']);
    }
}
