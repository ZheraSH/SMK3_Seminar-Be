<?php

namespace App\Http\Resources\Operator;

use App\Traits\ResolvesImageUrlTrait;
use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\RoleEnum;

class EmployeeResource extends JsonResource
{
    use ResolvesImageUrlTrait, HasEnumLabelsTrait;

    public function toArray($request): array
    {
        $user = $this->user;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $this->resolveImageUrl($this->image),
            'gender' => [
                'value' => $this->getEnumValue($this->gender),
                'label' => $this->getEnumLabel($this->gender),
            ],
            'phone_number' => $this->phone_number,
            'religion' => $this->religion?->name,
            'nip' => $this->nip,
            'nik' => $this->nik,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'roles' => $user?->roles?->map(fn ($role) => [
                'value' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
            ]),
        ];
    }
}
