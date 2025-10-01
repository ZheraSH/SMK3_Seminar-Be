<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\SubRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female',
            'role_id' => 'required|exists:roles,id',
            'sub_role_id' => 'nullable|exists:sub_roles,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', 422, $validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        // Assign role
        $user->roles()->attach($request->role_id, ['assigned_by' => Auth::id()]);

        // Assign sub role if provided
        if ($request->sub_role_id) {
            $user->subRoles()->attach($request->sub_role_id, ['assigned_by' => Auth::id()]);
        } else {
            // Assign default sub role for the role
            $defaultSubRole = SubRole::where('role_id', $request->role_id)
                                   ->where('is_default', true)
                                   ->first();
            if ($defaultSubRole) {
                $user->subRoles()->attach($defaultSubRole->id, ['assigned_by' => Auth::id()]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success([
            'user' => $user,
            'token' => $token,
        ], 'User registered successfully');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', 422, $validator->errors());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return ResponseHelper::error('Invalid credentials', 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success([
            'user' => $user,
            'token' => $token,
        ], 'Login successful');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseHelper::success(null, 'Logout successful');
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['roles', 'subRoles', 'student', 'employee']);

        return ResponseHelper::success($user, 'User profile retrieved successfully');
    }

    public function registerStudent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female',
            'nisn' => 'required|unique:students,nisn',
            'religion_id' => 'nullable|exists:religions,id',
            'birthdate' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'no_kk' => 'nullable|string|max:255',
            'no_birth_certificate' => 'nullable|string|max:255',
            'order_child' => 'nullable|integer|min:1',
            'count_siblings' => 'nullable|integer|min:0',
            'point' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', 422, $validator->errors());
        }

        // Get student role
        $studentRole = Role::where('name', 'siswa')->first();
        if (!$studentRole) {
            return ResponseHelper::error('Student role not found', 404);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        // Assign student role
        $user->roles()->attach($studentRole->id, ['assigned_by' => 1]); // Admin assigns

        // Create student profile
        $studentData = [
            'user_id' => $user->id,
            'nisn' => $request->nisn,
            'religion_id' => $request->religion_id,
            'birthdate' => $request->birthdate,
            'birthplace' => $request->birthplace,
            'address' => $request->address,
            'nik' => $request->nik,
            'no_kk' => $request->no_kk,
            'no_birth_certificate' => $request->no_birth_certificate,
            'order_child' => $request->order_child,
            'count_siblings' => $request->count_siblings,
            'point' => $request->point,
        ];

        $student = CrudHelper::create(new \App\Models\Student, $studentData);

        if (!$student) {
            // Rollback user if student creation fails
            $user->delete();
            return ResponseHelper::error('Failed to create student profile');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load(['roles', 'subRoles', 'student']);

        return ResponseHelper::success([
            'user' => $user,
            'token' => $token,
        ], 'Student registered successfully');
    }

    public function registerEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'gender' => 'nullable|in:male,female',
            'role_id' => 'required|exists:roles,id',
            'sub_role_id' => 'nullable|exists:sub_roles,id',
            'nip' => 'required|unique:employees,nip',
            'employment_status' => 'required|string',
            'religion_id' => 'nullable|exists:religions,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error('Validation failed', 422, $validator->errors());
        }

        // Validate role (must be guru or staff TU)
        $role = Role::find($request->role_id);
        if (!in_array($role->name, ['guru', 'staff TU'])) {
            return ResponseHelper::error('Role must be guru or staff TU', 422);
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        // Assign role
        $user->roles()->attach($request->role_id, ['assigned_by' => 1]); // Admin assigns

        // Assign sub role if provided
        if ($request->sub_role_id) {
            $user->subRoles()->attach($request->sub_role_id, ['assigned_by' => 1]);
        } else {
            // Assign default sub role for the role
            $defaultSubRole = SubRole::where('role_id', $request->role_id)
                                   ->where('is_default', true)
                                   ->first();
            if ($defaultSubRole) {
                $user->subRoles()->attach($defaultSubRole->id, ['assigned_by' => 1]);
            }
        }

        // Create employee profile
        $employeeData = [
            'user_id' => $user->id,
            'nip' => $request->nip,
            'employment_status' => $request->employment_status,
            'religion_id' => $request->religion_id,
        ];

        $employee = CrudHelper::create(new \App\Models\Employee, $employeeData);

        if (!$employee) {
            // Rollback user if employee creation fails
            $user->delete();
            return ResponseHelper::error('Failed to create employee profile');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load(['roles', 'subRoles', 'employee']);

        return ResponseHelper::success([
            'user' => $user,
            'token' => $token,
        ], 'Employee registered successfully');
    }
}