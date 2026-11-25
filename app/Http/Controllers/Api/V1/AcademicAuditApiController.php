<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AcademicAudit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicAuditApiController extends Controller
{
    /**
     * List academic audits with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AcademicAudit::query();

        if ($request->filled('audit_type')) {
            $query->where('audit_type', $request->audit_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('audit_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('audit_date', '<=', $request->date_to);
        }

        $audits = $query
            ->orderByDesc('audit_date')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $audits,
        ]);
    }

    /**
     * Create a new academic audit.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'audit_type' => ['required', Rule::in(['internal', 'external'])],
            'status' => ['required', Rule::in(['pending', 'completed'])],
            'audit_date' => ['required', 'date'],
            'findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'auditor_name' => ['nullable', 'string', 'max:255'],
        ]);

        $audit = AcademicAudit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Academic audit created successfully.',
            'data' => $audit,
        ], 201);
    }

    /**
     * Update an existing academic audit.
     */
    public function update(Request $request, AcademicAudit $academicAudit): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'audit_type' => ['required', Rule::in(['internal', 'external'])],
            'status' => ['required', Rule::in(['pending', 'completed'])],
            'audit_date' => ['required', 'date'],
            'findings' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'auditor_name' => ['nullable', 'string', 'max:255'],
        ]);

        $academicAudit->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Academic audit updated successfully.',
            'data' => $academicAudit->fresh(),
        ]);
    }

    /**
     * Delete an academic audit.
     */
    public function destroy(AcademicAudit $academicAudit): JsonResponse
    {
        $academicAudit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Academic audit deleted successfully.',
        ]);
    }
}