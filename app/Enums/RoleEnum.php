<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SCHOOL = "Operator Sekolah";
    case STUDENT = "Siswa";
    case TEACHER = "Guru Pengajar";
    case HOMEROOM_TEACHER = "Wali Kelas";
    case COUNSELOR = "BK";
    case STAFF = "Staff Tu";
    case CURRICULUM_COORDINATOR = "Waka Kurikulum";
}