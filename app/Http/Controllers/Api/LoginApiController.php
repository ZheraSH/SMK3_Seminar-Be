<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginApiController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $fullDomain = request()->root();

        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah'], 401);
        }

        $token = $user->createToken($request->email)->plainTextToken;
        $role = $user->roles->first()?->name ?? 'unknown';
        $defaultImage = asset('public/admin_assets/dist/images/profile/user-1.jpg');

        $image = $defaultImage;

        if ($role === 'student' && $user->student?->image) {
            $image = asset($fullDomain.'/storage/'.$user->student->image);
        } elseif ($role === 'staff' && $user->employee?->image) {
            $image = asset($fullDomain.'/storage/'.$user->employee->image);
        }

        return response()->json([
            'message' => 'Berhasil login',
            'token' => $token,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role,
                'password' => $user->password,
                'image' => $image,
            ],
        ]);
    }
}