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
        $user = $this->user;
        $photo = $this->image;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $this->resolveImageUrl(
                $photo,
                'admin_assets/dist/image/profile/teacher-boy.jpg'),
            'gender' => $this->gender?->label(),
            'phone_number' => $this->phone_number,
            'religion' => $this->religion?->name,
            'NIP' => $this->NIP,
            'NIK' => $this->NIK,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'roles' => $user?->roles?->map(fn ($role) => [
                'value' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
            ]),
            'subjects' => SubjectResource::collection($this->whenLoaded('subjects')),
        ];
    }
}