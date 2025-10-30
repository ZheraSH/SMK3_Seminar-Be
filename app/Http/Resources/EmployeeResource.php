<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $photo = $user?->image ?? $this->image;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $this->resolveImageUrl($photo),
            'gender' => $this->gender?->label(),
            'phone_number' => $this->phone_number,
            'religion' => $this->religion?->name,
            'NIP' => $this->NIP,
            'NIK' => $this->NIK,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'address' => $this->address,
            'roles' => $user?->roles?->map(function ($role) {
                return [
                    'value' => $role->name,
                    'label' => RoleEnum::tryFrom($role->name)?->label() ?? $role->name,
                ];
}) ?? [],
        ];
    }

    private function resolveImageUrl(?string $photo): string
    {
        if (!$photo) {
            return asset('admin_assets/dist/image/profile/teacher.jpg');
        }

        if (Storage::exists($photo)) {
            return url('storage/' . $photo);
        }

        if (file_exists(public_path($photo))) {
            return asset($photo);
        }

        return asset('admin_assets/dist/image/profile/teacher.jpg');
    }
}
