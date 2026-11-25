<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Domains\Academic\Models\Department;
use App\Domains\Faculty\Models\FacultyProfile;
use App\Domains\SAR\Models\SarAuditLog;

class StaffManagementApiController extends Controller
{
    /**
     * List all staff members with department and profile data
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('department', 'facultyProfile');

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->query('department_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('faculty_code', 'like', "%{$search}%");
            });
        }

        $staff = $query->orderBy('role')->orderBy('name')->get();
        $departments = Department::select('id', 'name', 'code')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'staff' => $staff,
                'departments' => $departments,
                'roles' => [
                    User::ROLE_SUPER_ADMIN,
                    User::ROLE_HOD,
                    User::ROLE_FACULTY,
                    User::ROLE_IQAC,
                    User::ROLE_EVALUATOR,
                ],
            ],
        ]);
    }

    /**
     * Super Admin create new staff member
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:Super Admin,HOD,Faculty,IQAC Admin,Evaluator',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:100',
            'faculty_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'highest_degree' => 'nullable|string|max:100',
            'teaching_experience_years' => 'nullable|integer|min:0',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'designation' => $validated['designation'] ?? 'Assistant Professor',
            'faculty_code' => $validated['faculty_code'] ?? ('FAC-' . rand(100, 999)),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // If creating faculty/HOD, also link to FacultyProfile
        if (in_array($validated['role'], [User::ROLE_FACULTY, User::ROLE_HOD])) {
            $deptId = $validated['department_id'] ?? (Department::first()?->id ?? 1);
            FacultyProfile::create([
                'department_id' => $deptId,
                'user_id' => $user->id,
                'faculty_id' => $user->faculty_code ?? ('FAC-' . $user->id),
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->phone,
                'joining_date' => now()->toDateString(),
                'highest_degree' => $validated['highest_degree'] ?? 'M.Tech',
                'teaching_experience_years' => $validated['teaching_experience_years'] ?? 3,
                'is_active' => true,
            ]);
        }

        // Audit Log
        SarAuditLog::create([
            'user_name' => 'Super Admin',
            'user_role' => 'Super Admin',
            'action' => 'STAFF_CREATED',
            'domain_module' => 'Staff',
            'description' => "Created new {$user->role} user: {$user->name} ({$user->email}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Staff member {$user->name} created successfully.",
            'user' => $user->load('department'),
        ], 201);
    }

    /**
     * Update existing staff profile
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => "required|email|unique:users,email,{$id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|in:Super Admin,HOD,Faculty,IQAC Admin,Evaluator',
            'department_id' => 'nullable|exists:departments,id',
            'designation' => 'nullable|string|max:100',
            'faculty_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?? null,
            'designation' => $validated['designation'] ?? $user->designation,
            'faculty_code' => $validated['faculty_code'] ?? $user->faculty_code,
            'phone' => $validated['phone'] ?? $user->phone,
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        if ($user->facultyProfile) {
            $fpUpdate = [
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->phone,
            ];
            if ($request->filled('highest_degree')) {
                $fpUpdate['highest_degree'] = $request->input('highest_degree');
            }
            if ($request->filled('designation')) {
                $fpUpdate['present_designation'] = $request->input('designation');
            }
            if ($request->filled('teaching_experience_years')) {
                $fpUpdate['teaching_experience_years'] = (int) $request->input('teaching_experience_years');
            }
            $user->facultyProfile->update($fpUpdate);
        }

        SarAuditLog::create([
            'user_name' => 'Super Admin',
            'user_role' => 'Super Admin',
            'action' => 'STAFF_UPDATED',
            'domain_module' => 'Staff',
            'description' => "Updated staff profile for {$user->name} ({$user->role}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Staff profile {$user->name} updated successfully.",
            'user' => $user->load('department'),
        ]);
    }

    /**
     * Toggle active/inactive status
     */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'Activated' : 'Deactivated';

        SarAuditLog::create([
            'user_name' => 'Super Admin',
            'user_role' => 'Super Admin',
            'action' => 'STAFF_STATUS_TOGGLED',
            'domain_module' => 'Staff',
            'description' => "{$statusText} user account {$user->name}.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "User account {$user->name} {$statusText}.",
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Delete staff member
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        SarAuditLog::create([
            'user_name' => 'Super Admin',
            'user_role' => 'Super Admin',
            'action' => 'STAFF_DELETED',
            'domain_module' => 'Staff',
            'description' => "Deleted staff user {$name}.",
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Staff member {$name} deleted.",
        ]);
    }
}
