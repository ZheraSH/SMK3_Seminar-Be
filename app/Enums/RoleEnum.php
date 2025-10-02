<?php

namespace App\Enums;

enum RoleEnum: string
{
    case STUDENT = "student";
    case TEACHER = "teacher";
    case SCHOOL = "operator_school";
    case STAFF = "staffTU";
}
