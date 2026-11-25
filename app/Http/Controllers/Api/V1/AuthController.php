<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Domains\SAR\Models\SarAuditLog;

class AuthController extends Controller
{
    /**
     * Authenticate user with role checks
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember', true))) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
            $user = Auth::user()->load('department');

            // Audit log
            SarAuditLog::create([
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'USER_LOGIN',
                'domain_module' => 'Auth',
                'description' => "User {$user->name} ({$user->role}) logged into SAR platform.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Welcome back, {$user->name}!",
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'designation' => $user->designation,
                    'faculty_code' => $user->faculty_code,
                    'department' => $user->department ? $user->department->name : 'Institution Wide',
                    'department_id' => $user->department_id,
                    'is_active' => $user->is_active,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password credentials.',
        ], 401);
    }

    /**
     * Get current authenticated user details
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
            ]);
        }

        $user->load('department');

        return response()->json([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'designation' => $user->designation,
                'faculty_code' => $user->faculty_code,
                'department' => $user->department ? $user->department->name : 'Institution Wide',
                'department_id' => $user->department_id,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Log the user out of application
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user) {
            SarAuditLog::create([
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'USER_LOGOUT',
                'domain_module' => 'Auth',
                'description' => "User {$user->name} logged out.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        }

        Auth::guard('web')->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }
}
