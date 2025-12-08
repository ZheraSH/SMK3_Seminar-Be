<?php

namespace App\Contracts\Repositories;

use App\Contracts\Interfaces\AttendanceMonitoringInterface;
use App\Enums\AttendanceStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceMonitoringRepository implements AttendanceMonitoringInterface
{
  public function getMonitoringData(array $filters): LengthAwarePaginator
  {
    $present = AttendanceStatusEnum::PRESENT->value;
    $sick  = AttendanceStatusEnum::SICK->value;
    $leave  = AttendanceStatusEnum::LEAVE->value;
    $alpha  = AttendanceStatusEnum::ALPHA->value;

    $query = DB::table('students AS s')
      ->join('users AS u', 'u.id', '=', 's.user_id')
      ->join('classroom_students AS cs', 'cs.student_id', '=', 's.id')
      ->join('classrooms AS c', 'c.id', '=', 'cs.classroom_id')
      ->leftJoin('majors AS m', 'm.id', '=', 'c.major_id')
      ->leftJoin('attendances AS a', function ($join) use ($filters, $present) {
                $join->on('a.student_id', '=', 's.id');
                if (!empty($filters['start_date'])) {
                    $join->whereDate('a.created_at', '>=', $filters['start_date']);
                }
                if (!empty($filters['end_date'])) {
                    $join->whereDate('a.created_at', '<=', $filters['end_date']);
                }
            })
      ->selectRaw('
        s.id AS student_id,
        u.name AS student_name,
        c.name AS classroom_name,
        m.code AS major_code,
        c.major_id,

        COALESCE(SUM(a.status = ?), 0) AS hadir,
        COALESCE(SUM(a.status = ?), 0) AS sakit,
        COALESCE(SUM(a.status = ?), 0) AS izin,
        COALESCE(SUM(a.status = ?), 0) AS alpha,

        COUNT(a.id) AS total_attendance,

        CASE
          WHEN COUNT(a.id) = 0 THEN 0
          ELSE ROUND((COALESCE(SUM(a.status = ?), 0) / COUNT(a.id)) * 100)
        END AS kehadiran
      ', [$present, $sick, $leave, $alpha, $present])
      ->groupBy('s.id', 'u.name', 'c.name', 'm.code', 'c.major_id');

    if (!empty($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('u.name', 'like', '%' . $filters['search'] . '%')
         ->orWhere('c.name', 'like', '%' . $filters['search'] . '%')
         ->orWhere('m.code', 'like', '%' . $filters['search'] . '%');
      });
    }

    if (!empty($filters['classroom'])) {
      $query->where('c.name', 'like', '%' . $filters['classroom'] . '%');
    }

    if (!empty($filters['major'])) {
      $query->where('m.code', 'like', '%' . $filters['major'] . '%');
    }

    $limit = $filters['limit'] ?? 15;

    return $query->paginate($limit);
  }

  public function getRecap(array $filters): array
  {
    $present = AttendanceStatusEnum::PRESENT->value;
    $sick  = AttendanceStatusEnum::SICK->value;
    $leave  = AttendanceStatusEnum::LEAVE->value;
    $alpha  = AttendanceStatusEnum::ALPHA->value;

    $query = DB::table('attendances AS a')
      ->selectRaw('
        COALESCE(SUM(a.status = ?), 0) AS total_hadir,
        COALESCE(SUM(a.status = ?), 0) AS total_sakit,
        COALESCE(SUM(a.status = ?), 0) AS total_izin,
        COALESCE(SUM(a.status = ?), 0) AS total_alpha
      ', [$present, $sick, $leave, $alpha])
      ->whereDate('a.created_at', '=', now()->format('Y-m-d')) // Filter hanya data hari ini
      ->join('students AS s', 's.id', '=', 'a.student_id')
      ->join('classroom_students AS cs', 'cs.student_id', '=', 's.id')
      ->join('classrooms AS c', 'c.id', '=', 'cs.classroom_id')
      ->leftJoin('majors AS m', 'm.id', '=', 'c.major_id')
      ->join('users AS u', 'u.id', '=', 's.user_id');

    if (!empty($filters['search'])) {
      $query->where(function ($q) use ($filters) {
        $q->where('u.name', 'like', '%' . $filters['search'] . '%')
        ->orWhere('c.name', 'like', '%' . $filters['search'] . '%');
      });
    }

    if (!empty($filters['classroom'])) {
      $query->where('c.name', 'like', '%' . $filters['classroom'] . '%');
    }

    if (!empty($filters['major'])) {
      $query->where('m.code', 'like', '%' . $filters['major'] . '%');
    }

    $result = $query->first();

    return [
      'jumlah_siswa_hadir_hari_ini' => (int) ($result->total_hadir ?? 0),
      'jumlah_siswa_sakit_hari_ini' => (int) ($result->total_sakit ?? 0),
      'jumlah_siswa_izin_hari_ini' => (int) ($result->total_izin ?? 0),
      'jumlah_siswa_alpha_hari_ini' => (int) ($result->total_alpha ?? 0),
    ];
  }

  public function getFilterOptions(): array
  {
    $classrooms = DB::table('classrooms AS c')
      ->leftJoin('majors AS m', 'm.id', '=', 'c.major_id')
      ->select('c.id', 'c.name AS classroom_name', 'm.code AS major_code', 'm.id AS major_id')
      ->orderBy('c.name')
      ->get();

    $majors = DB::table('majors')
      ->select('id', 'code AS major_code')
      ->orderBy('code')
      ->get();

    return [
      'classrooms' => $classrooms,
      'majors' => $majors
    ];
  }

  public function syncLatestData(): bool
  {
    return true;
  }
}