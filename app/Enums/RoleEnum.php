<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SCHOOL = 'school_operator';
    case STUDENT = 'student';
    case TEACHER = 'teacher';
    case HOMEROOM_TEACHER = 'homeroom_teacher';
    case COUNSELOR = 'counselor';
    case STAFF = 'staff_tu';
    case CURRICULUM_COORDINATOR = 'curriculum_coordinator';

    public function label(): string
    {
        return match ($this) {
            self::SCHOOL => 'Operator Sekolah',
            self::STUDENT => 'Siswa',
            self::TEACHER => 'Guru Pengajar',
            self::HOMEROOM_TEACHER => 'Wali Kelas',
            self::COUNSELOR => 'Bimbingan Konseling',
            self::STAFF => 'Staff Tata Usaha',
            self::CURRICULUM_COORDINATOR => 'Waka Kurikulum',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toArray(): array
    {
        $data = [];
        foreach (self::cases() as $case) {
            $data[$case->value] = $case->label();
        }

        return $data;
    }

    public static function teacherRoles(): array
    {
        return [
            self::TEACHER->value,
            self::HOMEROOM_TEACHER->value,
            self::COUNSELOR->value,
        ];
    }

    public static function staffRoles(): array
    {
        return [
            self::STAFF->value,
            self::CURRICULUM_COORDINATOR->value,
        ];
    }

    public static function isTeacherRole(string $role): bool
    {
        return in_array($role, self::teacherRoles(), true);
    }

    public static function isStaffRole(string $role): bool
    {
        return in_array($role, self::staffRoles(), true);
    }
}
