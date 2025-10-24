<?php

namespace App\Http\Resources;

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
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $photo && Storage::exists($photo)
                ? url('storage/' . $photo)
                : asset('admin_assets/dist/image/profile/user-1.jpg'),
            'gender' => $this->gender,
            'phone_number'=> $this->phone_number,
            'religion_id' => $this->religion?->name,
            'NIP' => $this->NIP,
            'NIK' => $this->NIK,
            'birth_place'=> $this->birth_place, 
            'birth_date' => $this->birth_date, 
            'address'=> $this->address,
        ];
    }
}
