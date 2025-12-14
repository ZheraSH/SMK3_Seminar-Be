<?php

namespace App\Enums;

enum UploadDiskEnum: string
{
    case LOGO = "logo";
    case STUDENT = "student";
    case TEACHER = "teacher";
    case STAFF = "staff";
    case HOMEROOM_TEACHER = "home_teacher";
    case PROOF = "proof";
    case ATTENDANCE_JOURNAL = "attendance_journal";

}