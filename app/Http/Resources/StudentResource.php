<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
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
            'nisn' => $this->nisn,
            'gender' => $this->gender,
            'religion_name' => $this->religion?->name,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date,
            'number_kk' => $this->number_kk,
            'number_akta' => $this->number_akta,
            'order_child' => $this->order_child,
            'count_siblings' => $this->count_siblings,
            'address' => $this->address,
        ];
    }
}
