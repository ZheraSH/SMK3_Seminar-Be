<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonHourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'start' => $this->start,
            'end'   => $this->end,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
