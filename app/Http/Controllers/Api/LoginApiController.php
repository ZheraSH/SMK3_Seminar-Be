<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginApiController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah'], 401);
        }

        $token = $user->createToken($request->email)->plainTextToken;

        $role = $user->roles->first()?->name ?? 'unknown';
        $roleEnum = collect(RoleEnum::cases())->firstWhere('value', $role);

        $defaultImage = asset('public/admin_assets/dist/images/profile/user-1.jpg');
        $image = $defaultImage;

        if ($roleEnum === RoleEnum::STUDENT && $user->student?->image) {
            $image = asset('storage/' . $user->student->image);
        } elseif (
            in_array($roleEnum, [RoleEnum::STAFF, RoleEnum::TEACHER], true)
            && $user->employee?->image
        ) {
            $image = asset('storage/' . $user->employee->image);
        }

        return response()->json([
            'message' => 'Berhasil login',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleEnum?->value ?? 'unknown',
                'image' => $image,
            ],
        ]);
    }
}