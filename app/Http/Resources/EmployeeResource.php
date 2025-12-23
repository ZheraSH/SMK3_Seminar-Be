<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use App\Traits\ResolvesImageUrlTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'image' => $this->resolveImageUrl($this->image),
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'nip' => $this->nip,
            'nik' => $this->nik,
            'gender' => $this->gender ? [
                'value' => $this->gender->value,
                'label' => $this->gender->label(),
            ] : null,
            'religion' => $this->religion ? [
                'id' => $this->religion->id,
                'name' => $this->religion->name,
            ] : null,
            'birth' => [
                'place' => $this->birth_place,
                'date' => $this->birth_date,
            ],
            'roles' => $this->user?->roles?->map(fn ($role) => [
                'value' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
            ])->values(),
            'subjects' => SubjectResource::collection(
                $this->whenLoaded('subjects')
            ),
        ];
    }
}
