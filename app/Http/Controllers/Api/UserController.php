<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\SubRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'subRoles', 'student', 'employee'])->get();
        return ResponseHelper::success($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'gender' => 'nullable|in:male,female',
            'role_id' => 'required|exists:roles,id',
            'sub_role_id' => 'nullable|exists:sub_roles,id',
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'gender' => $validated['gender'],
        ];

        $user = CrudHelper::create(new User, $userData);

        if (!$user) return ResponseHelper::error('Failed to create user');

        // Assign role
        $user->roles()->attach($validated['role_id'], ['assigned_by' => Auth::id()]);

        // Assign sub role if provided
        if (isset($validated['sub_role_id'])) {
            $user->subRoles()->attach($validated['sub_role_id'], ['assigned_by' => Auth::id()]);
        } else {
            // Assign default sub role for the role
            $defaultSubRole = SubRole::where('role_id', $validated['role_id'])
                                   ->where('is_default', true)
                                   ->first();
            if ($defaultSubRole) {
                $user->subRoles()->attach($defaultSubRole->id, ['assigned_by' => Auth::id()]);
            }
        }

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success($user, 'User created successfully');
    }

    public function show($id)
    {
        $user = User::with(['roles', 'subRoles', 'student', 'employee'])->find($id);
        
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }
        
        return ResponseHelper::success($user, 'User retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'gender' => 'sometimes|nullable|in:male,female',
            'role_id' => 'sometimes|exists:roles,id',
            'sub_role_id' => 'sometimes|nullable|exists:sub_roles,id',
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['email'])) $updateData['email'] = $validated['email'];
        if (isset($validated['password'])) $updateData['password'] = Hash::make($validated['password']);
        if (isset($validated['gender'])) $updateData['gender'] = $validated['gender'];

        if (!empty($updateData)) {
            $updated = CrudHelper::update($user, $updateData);
            if (!$updated) return ResponseHelper::error('Failed to update user');
        }

        // Update role if provided
        if (isset($validated['role_id'])) {
            $user->roles()->sync([$validated['role_id'] => ['assigned_by' => Auth::id()]]);
        }

        // Update sub role if provided
        if (isset($validated['sub_role_id'])) {
            if ($validated['sub_role_id']) {
                $user->subRoles()->sync([$validated['sub_role_id'] => ['assigned_by' => Auth::id()]]);
            } else {
                $user->subRoles()->detach();
            }
        }

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success($user, 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }

        // Prevent deleting own account
        if ($user->id === Auth::id()) {
            return ResponseHelper::error('Cannot delete your own account', 422);
        }

        if (!CrudHelper::delete($user)) {
            return ResponseHelper::error('Failed to delete user');
        }

        return ResponseHelper::success(null, 'User deleted successfully');
    }

    public function assignRole(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'sub_role_id' => 'nullable|exists:sub_roles,id',
        ]);

        // Assign role
        $user->roles()->sync([$validated['role_id'] => ['assigned_by' => Auth::id()]]);

        // Assign sub role if provided
        if (isset($validated['sub_role_id'])) {
            $user->subRoles()->sync([$validated['sub_role_id'] => ['assigned_by' => Auth::id()]]);
        } else {
            // Assign default sub role for the role
            $defaultSubRole = SubRole::where('role_id', $validated['role_id'])
                                   ->where('is_default', true)
                                   ->first();
            if ($defaultSubRole) {
                $user->subRoles()->sync([$defaultSubRole->id => ['assigned_by' => Auth::id()]]);
            }
        }

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success($user, 'Role assigned successfully');
    }

    public function removeRole(Request $request, $id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return ResponseHelper::notFound('User not found');
        }

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->detach($validated['role_id']);

        $user->load(['roles', 'subRoles']);

        return ResponseHelper::success($user, 'Role removed successfully');
    }
}