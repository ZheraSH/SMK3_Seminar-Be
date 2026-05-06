<?php

namespace App\Http\Resources\Operator;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $data = $this->resource;
        $dest = $data['destination_classroom'];
        $src = $data['source_classroom'];

        return [
            'promotion' => [
                'from_level' => $data['from_level'],
                'to_level' => $data['to_level'],
                'from_school_year' => $data['from_school_year'],
                'to_school_year' => $data['to_school_year'],
                'students_promoted' => $data['students_promoted'],
            ],
            'source_classroom' => [
                'id' => $src->id,
                'name' => $src->name,
                'level' => $src->levelClass?->name,
                'major' => $src->major?->name,
                'school_year' => $src->schoolYear?->name,
                'homeroom_teacher' => $src->homeroomTeacher?->user?->name,
            ],
            'destination_classroom' => [
                'id' => $dest->id,
                'name' => $dest->name,
                'level' => $dest->levelClass?->name,
                'major' => $dest->major?->name,
                'school_year' => $dest->schoolYear?->name,
                'homeroom_teacher' => $dest->homeroomTeacher?->user?->name,
            ],
        ];
    }
}
