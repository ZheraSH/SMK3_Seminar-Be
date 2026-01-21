<?php

namespace App\Http\Resources\Profile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this;
        $profile = $user->student ?? $user->employee;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $profile?->image ? asset('storage/' . $profile->image) : null,
            'profile_type' => $user->student ? 'student' : ($user->employee ? 'employee' : null),
            'address' => $profile?->address,
            'is_student' => $user->student !== null,
            'is_employee' => $user->employee !== null,
        ];
    }
}
