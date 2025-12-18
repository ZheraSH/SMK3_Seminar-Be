<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user'  => new UserResource($this['user']),
            'role'  => $this['role'],
            'token' => $this['token'],
        ];
    }
}
