<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use App\Traits\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray(Request $request): array
    {
        $user = $this->user;
        $photo = $user?->image ?? $this->image;

        return [
            'id' => $this->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $this->resolveImageUrl($photo, 'admin_assets/dist/image/profile/teacher.jpg'),
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
            'subjects' => $this->whenLoaded('subjects', function () {
                if ($this->subjects->isEmpty()) {
                    return [
                        'message' => 'Guru ini belum memiliki mapel yang assign di lessonShcedule'
                    ];
                }
                
                return $this->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                    ];
                });
            }, [
                'message' => 'Data mapel belum dimuat'
            ]),
        ];
    }
}