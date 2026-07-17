<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;

/**
 * Role Management use case (SDD Table 30): "Assign, Modify and Enforce
 * User Roles and Access Permissions". Restricted to Administrators via the
 * 'role:Administrator' route middleware.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn ($q) => $q->where('full_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->paginate(20);

        return response()->json($users);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['roles', 'memberships.group']));
    }

    /**
     * Step 3-5: admin selects a new role and the backend updates the
     * user's role designation and logs an audit trail entry.
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:Student,Lecturer,Administrator',
        ]);

        $role = Role::firstOrCreate(['role_name' => $request->role]);

        UserRole::updateOrCreate(
            ['user_id' => $user->user_id, 'role_id' => $role->role_id],
            ['assigned_at' => now(), 'assigned_by' => $request->user()->user_id]
        );

        // Step 6: invalidate existing tokens so the client is forced to
        // re-authenticate and receive a token embedded with the new role.
        $user->tokens()->delete();

        return response()->json([
            'message' => "Role '{$request->role}' assigned to {$user->full_name}.",
            'user' => $user->fresh('roles'),
        ]);
    }
}
