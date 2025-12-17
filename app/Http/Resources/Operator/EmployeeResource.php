<?php

namespace App\Http\Resources\Operator;

use App\Traits\Resources\ResolvesImageUrlTrait;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\RoleEnum;

class EmployeeResource extends JsonResource
{
    use ResolvesImageUrlTrait, HasEnumLabelsTrait;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'image' => $this->resolveImageUrl($this->image),
            'gender' => [
                'value' => $this->getEnumValue($this->gender),
                'label' => $this->getEnumLabel($this->gender),
            ],
            'phone_number' => $this->phone_number,
            'religion' => [
                'id' => $this->religion?->id,
                'name' => $this->religion?->name,
            ],
            'nip' => $this->nip,
            'nik' => $this->nik,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'roles' => $this->user?->roles?->map(fn ($role) => [
                'value' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
            ]),
        ];
    }
}
