<?php

namespace App\Enums;

enum UploadDiskEnum: string
{
    case LOGO = "logo";
    case STUDENT = "student";
    case TEACHER = "teacher";
    // case STAFF = "staff";
    case ATTENDANCE_JOURNAL = "attendance_journal";
    case PROOF = "proof";
    // case PROOF_REPAIR = "proof_repair";
}