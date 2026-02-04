<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolLogoResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    /**
     * Transform the resource into an array.
     * Resource khusus untuk public endpoint - hanya mengirim logo
     */
    public function toArray(Request $request): array
    {
        return [
            'logo' => $this->resolveImageUrl($this->logo),
        ];
    }
}
