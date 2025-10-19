<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource->user ?? null;
        $photo = $user?->image;

        return [
            'image' => $photo && Storage::exists($photo)
                ? url('storage/' . $photo)
                : asset('admin_assets/dist/image/profile/user-1.jpg'),
            'name' => $user?->name,
            'Nisn' => $user->nisn,
            'gender' => $user->gender,
            'religion_id' => $user->religion?->id,
            'birth_place'=> $user->birth_place, 
            'birth_date' => $user->birth_date,
            'number_kk' => $user->number_kk,
            'number_akta' => $user->number_akta,
            'order_child'=> $user->order_child, 
            'address'=> $user->address,
        ];
    }
}
