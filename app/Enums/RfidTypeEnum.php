<?php

namespace App\Enums;

enum RfidTypeEnum: string
{
    case STUDENT = "App\Models\Student";
    case EMPLOYEE = "App\Models\Employee";
}
