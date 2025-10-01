<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubRole;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Helpers\CrudHelper;

class SubRoleController extends Controller
{
    public function index(Request $request)
    {
        $query = SubRole::with('role');
        
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        $subRoles = $query->get();
        return ResponseHelper::success($subRoles, 'Sub roles retrieved successfully');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'role_id' => 'required|exists:roles,id',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, unset other defaults for the same role
        if ($validated['is_default']) {
            SubRole::where('role_id', $validated['role_id'])
                   ->where('is_default', true)
                   ->update(['is_default' => false]);
        }

        $subRole = CrudHelper::create(new SubRole, $validated);

        if (!$subRole) return ResponseHelper::error('Failed to create sub role');

        $subRole->load('role');

        return ResponseHelper::success($subRole, 'Sub role created successfully');
    }

    public function show($id)
    {
        $subRole = SubRole::with('role')->find($id);
        
        if (!$subRole) {
            return ResponseHelper::notFound('Sub role not found');
        }
        
        return ResponseHelper::success($subRole, 'Sub role retrieved successfully');
    }

    public function update(Request $request, $id)
    {
        $subRole = SubRole::find($id);
        
        if (!$subRole) {
            return ResponseHelper::notFound('Sub role not found');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:500',
            'role_id' => 'sometimes|exists:roles,id',
            'is_default' => 'sometimes|boolean',
        ]);

        // If this is set as default, unset other defaults for the same role
        if (isset($validated['is_default']) && $validated['is_default']) {
            $roleId = $validated['role_id'] ?? $subRole->role_id;
            SubRole::where('role_id', $roleId)
                   ->where('id', '!=', $subRole->id)
                   ->where('is_default', true)
                   ->update(['is_default' => false]);
        }

        $updated = CrudHelper::update($subRole, $validated);

        if (!$updated) return ResponseHelper::error('Failed to update sub role');

        $updated->load('role');

        return ResponseHelper::success($updated, 'Sub role updated successfully');
    }

    public function destroy($id)
    {
        $subRole = SubRole::find($id);
        
        if (!$subRole) {
            return ResponseHelper::notFound('Sub role not found');
        }

        // Check if sub role is being used by users
        if ($subRole->users()->count() > 0) {
            return ResponseHelper::error('Cannot delete sub role that is being used by users', 422);
        }

        if (!CrudHelper::delete($subRole)) {
            return ResponseHelper::error('Failed to delete sub role');
        }

        return ResponseHelper::success(null, 'Sub role deleted successfully');
    }
}