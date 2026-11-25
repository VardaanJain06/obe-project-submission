<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Curriculum\Models\CoPoMapping;
use App\Domains\Academic\Models\Program;
use App\Domains\Curriculum\Services\ArticulationMatrixService;
use App\Domains\SAR\Models\SarAuditLog;

class ArticulationMatrixApiController extends Controller
{
    protected ArticulationMatrixService $matrixService;

    public function __construct(ArticulationMatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
    }

    /**
     * Get Course Articulation Matrix
     */
    public function getCourseMatrix(int $courseId): JsonResponse
    {
        $course = Course::with('program', 'courseOutcomes')->find($courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $matrix = $this->matrixService->getCourseMatrix($course);

        return response()->json([
            'success' => true,
            'data' => $matrix,
        ]);
    }

    /**
     * Get Program-level Articulation Matrix (All courses vs PO1..PO12 & PSO1..PSO3)
     */
    public function getProgramMatrix(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();

        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Program not found'], 404);
        }

        $matrix = $this->matrixService->getProgramMatrix($program);

        return response()->json([
            'success' => true,
            'data' => $matrix,
        ]);
    }

    /**
     * Update CO-PO or CO-PSO correlation mapping inline
     */
    public function updateMapping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_outcome_id' => 'required|exists:course_outcomes,id',
            'target_type' => 'required|in:PO,PSO',
            'target_id' => 'required|integer',
            'correlation_level' => 'nullable|integer|between:0,3', // 0 or null removes/zeros it, 1..3 sets level
            'justification' => 'nullable|string|max:500',
        ]);

        $co = CourseOutcome::with('course')->findOrFail($validated['course_outcome_id']);

        if (empty($validated['correlation_level']) || $validated['correlation_level'] == 0) {
            CoPoMapping::where('course_outcome_id', $co->id)
                ->where('target_type', $validated['target_type'])
                ->where('target_id', $validated['target_id'])
                ->delete();
            $level = null;
        } else {
            $mapping = CoPoMapping::updateOrCreate(
                [
                    'course_outcome_id' => $co->id,
                    'target_type' => $validated['target_type'],
                    'target_id' => $validated['target_id'],
                ],
                [
                    'correlation_level' => $validated['correlation_level'],
                    'justification' => $validated['justification'] ?? "Updated mapping for {$co->co_code}",
                ]
            );
            $level = $mapping->correlation_level;
        }

        // Audit Log
        SarAuditLog::create([
            'sar_report_id' => null,
            'user_name' => 'Faculty / Course Coordinator',
            'user_role' => 'Faculty',
            'action' => 'CO_PO_MODIFIED',
            'domain_module' => 'Curriculum',
            'payload' => $validated,
            'description' => "Updated {$co->co_code} mapping to {$validated['target_type']}#{$validated['target_id']} to level {$level}.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Articulation matrix updated successfully',
            'updated_level' => $level,
        ]);
    }
}
