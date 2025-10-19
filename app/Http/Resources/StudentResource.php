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
            'name' => $user?->name,
            'email' => $user?->email,
            'image' => $photo && Storage::exists($photo)
                ? url('storage/' . $photo)
                : asset('admin_assets/dist/image/profile/user-1.jpg'),
        ];
    }
}
