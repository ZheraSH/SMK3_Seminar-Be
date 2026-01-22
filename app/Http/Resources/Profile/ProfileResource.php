<?php

namespace App\Http\Resources\Profile;

use App\Traits\Resources\HasEnumLabelsTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    use HasEnumLabelsTrait;

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
            'gender' => [
                'value' => $this->getEnumValue($profile?->gender),
                'label' => $this->getEnumLabel($profile?->gender),
            ],
            'address' => $profile?->address,
            'is_student' => $user->student !== null,
            'is_employee' => $user->employee !== null,
        ];
    }
}
