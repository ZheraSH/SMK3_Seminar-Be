<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'major' => $this->major?->name,
            'level_class' => $this->levelclass?->name,
            'school_year' => $this->schoolyear?->school_year,
            'teacher' => $this->teacher?->user?->name,
            'teacher_ID' => $this->teacher_id,
        ];
    }
}