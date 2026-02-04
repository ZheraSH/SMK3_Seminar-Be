<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolLogoResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray(Request $request): array
    {
        return [
            'logo' => $this->resolveImageUrl($this->logo),
            'name' => $this->name,
        ];
    }
}
