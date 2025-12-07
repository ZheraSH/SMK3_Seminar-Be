<?php

namespace App\Http\Resources;

use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
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
            return null;
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }

        return asset($imagePath);
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