<?php

namespace App\Http\Resources\Homeroom_teacher;

use Illuminate\Http\Resources\Json\JsonResource;

class SummaryClassResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'classroom' => [
                'id' => $this['classroom_id'],
                'name' => $this['classroom_name'],
            ],
            'tahun_ajaran' => $this['tahun_ajaran'] ?? null,
            'total_students' => $this['total_students'],
        ];
    }
}
