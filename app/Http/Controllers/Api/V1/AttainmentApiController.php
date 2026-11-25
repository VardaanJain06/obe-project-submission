<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Attainment\Services\AttainmentEngine;
use App\Domains\Attainment\Services\DirectAttainmentService;
use App\Domains\Attainment\Services\IndirectAttainmentService;

class AttainmentApiController extends Controller
{
    protected AttainmentEngine $attainmentEngine;
    protected DirectAttainmentService $directService;
    protected IndirectAttainmentService $indirectService;

    public function __construct(
        AttainmentEngine $attainmentEngine,
        DirectAttainmentService $directService,
        IndirectAttainmentService $indirectService
    ) {
        $this->attainmentEngine = $attainmentEngine;
        $this->directService = $directService;
        $this->indirectService = $indirectService;
    }

    /**
     * Get Complete Final PO and PSO Attainment Data
     */
    public function getFinalAttainment(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();

        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Program not found'], 404);
        }

        $academicYearId = $request->query('academic_year_id');
        $year = $academicYearId ? AcademicYear::find($academicYearId) : null;

        $results = $this->attainmentEngine->computeFinalProgramAttainment($program, $year);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get Course Direct Attainment
     */
    public function getCourseDirectAttainment(int $courseId): JsonResponse
    {
        $course = Course::with('courseOutcomes', 'program')->find($courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $year = AcademicYear::where('type', 'CAY')->first() ?? AcademicYear::first();
        $res = $this->directService->computeCourseDirectAttainment($course, $year);

        return response()->json([
            'success' => true,
            'data' => $res,
        ]);
    }

    /**
     * Get Course Performance Year-on-Year
     */
    public function getCoursePerformance(int $courseId): JsonResponse
    {
        $course = Course::with('courseOutcomes', 'program')->find($courseId);
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $res = $this->directService->computeCoursePerformanceYearOnYear($course);

        return response()->json([
            'success' => true,
            'data' => $res,
        ]);
    }

    /**
     * Get Indirect Attainment Breakdown (Exit, Alumni, Employer surveys)
     */
    public function getIndirectAttainment(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();
        $year = AcademicYear::where('type', 'CAY')->first() ?? AcademicYear::first();

        if (!$program || !$year) {
            return response()->json(['success' => false, 'message' => 'Program or Year not found'], 404);
        }

        $res = $this->indirectService->computeProgramIndirectAttainment($program, $year);

        return response()->json([
            'success' => true,
            'data' => $res,
        ]);
    }
}
