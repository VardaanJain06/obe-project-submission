<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\CoPoMapping;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\SAR\Models\SarAuditLog;

class CourseManagementApiController extends Controller
{
    /**
     * List all courses with CO count and program details
     */
    public function index(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();

        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Program not found'], 404);
        }

        $courses = Course::with('courseOutcomes')
            ->where('program_id', $program->id)
            ->orderBy('semester')
            ->orderBy('course_code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'program' => $program,
                'courses' => $courses,
            ],
        ]);
    }

    /**
     * Create new Course
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:150',
            'semester' => 'required|integer|between:1,8',
            'credits' => 'required|numeric|between:1,6',
            'lecture_hours' => 'nullable|integer|min:0',
            'tutorial_hours' => 'nullable|integer|min:0',
            'practical_hours' => 'nullable|integer|min:0',
            'course_type' => 'nullable|string|max:50',
        ]);

        $year = AcademicYear::where('type', 'CAY')->first() ?? AcademicYear::first();
        $validated['academic_year_id'] = $year?->id;

        $course = Course::create($validated);

        // Automatically seed 5 default COs for quick setup
        for ($i = 1; $i <= 5; $i++) {
            $co = CourseOutcome::create([
                'course_id' => $course->id,
                'co_code' => "CO{$i}",
                'description' => "Understand and apply core concepts of {$course->course_name} module {$i}.",
                'target_percentage' => 60.0,
                'target_level' => 2.5,
                'bloom_level' => $i <= 2 ? 'Understand' : ($i <= 4 ? 'Apply' : 'Analyze'),
            ]);

            // Auto-map to PO1, PO2, PO3
            $pos = ProgramOutcome::where('program_id', $course->program_id)->take(3)->get();
            foreach ($pos as $po) {
                CoPoMapping::create([
                    'course_outcome_id' => $co->id,
                    'target_type' => 'PO',
                    'target_id' => $po->id,
                    'correlation_level' => 2,
                    'justification' => 'Direct alignment with outcome competency.',
                ]);
            }
        }

        SarAuditLog::create([
            'user_name' => 'Course Coordinator',
            'user_role' => 'Faculty',
            'action' => 'COURSE_CREATED',
            'domain_module' => 'Curriculum',
            'description' => "Added new course {$course->course_code}: {$course->course_name} (Sem {$course->semester}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Course {$course->course_code} created successfully with 5 Course Outcomes.",
            'data' => $course->load('courseOutcomes'),
        ], 201);
    }

    /**
     * Add new Course Outcome (CO) to a Course
     */
    public function addCourseOutcome(Request $request, int $courseId): JsonResponse
    {
        $course = Course::findOrFail($courseId);

        $validated = $request->validate([
            'co_code' => 'required|string|max:10',
            'description' => 'required|string|max:500',
            'target_percentage' => 'required|numeric|between:40,100',
            'target_level' => 'required|numeric|between:1,3',
            'bloom_level' => 'required|string|in:Remember,Understand,Apply,Analyze,Evaluate,Create',
        ]);

        $co = CourseOutcome::create([
            'course_id' => $course->id,
            'co_code' => $validated['co_code'],
            'description' => $validated['description'],
            'target_percentage' => $validated['target_percentage'],
            'target_level' => $validated['target_level'],
            'bloom_level' => $validated['bloom_level'],
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$co->co_code} added to {$course->course_code}.",
            'data' => $co,
        ], 201);
    }

    /**
     * Delete Course
     */
    public function destroy(int $id): JsonResponse
    {
        $course = Course::findOrFail($id);
        $code = $course->course_code;
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => "Course {$code} deleted.",
        ]);
    }
}
