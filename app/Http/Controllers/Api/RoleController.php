<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('subRoles')->get();
        return ResponseHelper::success($roles, 'Roles retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
        ]);

        $role = CrudHelper::create(new Role, $validated);

        if (!$role) return ResponseHelper::error('Failed to create role');

        $role->load('subRoles');

        return ResponseHelper::success($role, 'Role created successfully');
    }

    public function show($id)
    {
        $role = Role::with('subRoles')->find($id);
        
        if (!$role) {
            return ResponseHelper::notFound('Role not found');
        }
        
        return ResponseHelper::success($role, 'Role retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return ResponseHelper::notFound('Role not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'sometimes|nullable|string|max:500',
        ]);

        $updated = CrudHelper::update($role, $validated);

        if (!$updated) return ResponseHelper::error('Failed to update role');

        $updated->load('subRoles');

        return ResponseHelper::success($updated, 'Role updated successfully');
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return ResponseHelper::notFound('Role not found');
        }

        // Check if role is being used by users
        if ($role->users()->count() > 0) {
            return ResponseHelper::error('Cannot delete role that is being used by users', 422);
        }

        if (!CrudHelper::delete($role)) {
            return ResponseHelper::error('Failed to delete role');
        }

        return ResponseHelper::success(null, 'Role deleted successfully');
    }
}