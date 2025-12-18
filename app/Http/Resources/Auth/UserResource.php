<?php

namespace App\Http\Resources\Auth;

use App\Enums\RoleEnum;
use App\Traits\Resources\ResolvesImageUrlTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    use ResolvesImageUrlTrait;

    public function toArray(Request $request): array
    {
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $this->getImageUrl($user),
            'employee_id' => $user->employee?->id,
            'student_id' => $user->student?->id,
            'roles' => $this->mapRoles($user->roles),
        ];
    }

    private function getImageUrl($user): ?string
    {
        $imagePath = $user->employee?->image ?? $user->student?->image ?? null;

        if (!$imagePath) {

            return $this->resolveImageUrl(null);
        }

        return $this->resolveImageUrl($imagePath);
    }

    private function mapRoles($roles): array
    {
        return $roles->map(function ($role) {
            $roleEnum = RoleEnum::tryFrom($role->name);
            
            return [
                'id' => $role->id,
                'value' => $role->name,
                'label' => $roleEnum ? $roleEnum->label() : ucfirst(str_replace('_', ' ', $role->name)),
            ];
        })->toArray();
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode(200);
    }
}