<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\HasEnumLabelsTrait;
use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    use ResolvesImageUrlTrait, HasEnumLabelsTrait;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'logo' => $this->resolveImageUrl($this->logo),
            'name' => $this->name,
            'principal_name' => $this->principal_name,
            'npsn' => $this->npsn,
            'phone' => $this->phone,
            'email' => $this->email,
            'school_type' => [
                'value' => $this->getEnumValue($this->school_type),
                'label' => $this->getEnumLabel($this->school_type),
            ],
            'accreditation' => [
                'value' => $this->getEnumValue($this->accreditation),
                'label' => $this->getEnumLabel($this->accreditation),
            ],
            'address' => $this->address,
        ];
    }
}
