<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->student->user->name,
            'email' => $this->student->user->email,
            'image' => $this->student->user->image != null ? asset(request()->root(). '/storage'.$this->student->image) : asset(request()->root().'/public/admin_assets/dist/image/profile/user-1.jpg'),
        ];
    }
}
