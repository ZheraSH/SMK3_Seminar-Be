<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SCHOOL = "school_operator";
    case TEACHER = "teacher";
    case STUDENT = "student";
    case STAFF = "staff_tu";
}
