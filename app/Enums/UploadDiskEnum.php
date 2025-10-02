<?php

namespace App\Enums;

enum UploadDiskEnum: string
{
    case LOGO = "logo";
    case TEACHER = "teacher";
    case STAFF = "staff";
    case STUDENT = "student";
    case ATTENDANCE_JOURNAL = "attendance_journal";

    case PROOF_REPAIR = "proof_repair";
    case PROOF = "proof";
}
