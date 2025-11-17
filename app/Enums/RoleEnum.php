<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SCHOOL = "school_operator";
    case STUDENT = "student";
    case TEACHER = "teacher";
    case HOMEROOM_TEACHER = "homeroom_teacher";
    case COUNSELOR = "counselor";
    case STAFF = "staff_tu";
    case CURRICULUM_COORDINATOR = "curriculum_coordinator";

    public function label(): string
    {
        return match($this) {
            self::SCHOOL => 'Operator Sekolah',
            self::STUDENT => 'Siswa',
            self::TEACHER => 'Guru Pengajar',
            self::HOMEROOM_TEACHER => 'Wali Kelas',
            self::COUNSELOR => 'BK',
            self::STAFF => 'Staff TU',
            self::CURRICULUM_COORDINATOR => 'Waka Kurikulum',
        };
    }

    public static function values(): array
{
    return array_column(self::cases(), 'value');
}
}