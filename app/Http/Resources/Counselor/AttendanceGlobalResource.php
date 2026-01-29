<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceGlobalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'counts' => $this->resource['counts'],
            'percentages' => $this->resource['percentages'],
        ];
    }
}
