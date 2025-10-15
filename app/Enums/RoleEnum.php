<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SCHOOL = "operator_school";
    case TEACHER = "teacher";
    case STUDENT = "student";
    case STAFF = "staff_tu";
}
