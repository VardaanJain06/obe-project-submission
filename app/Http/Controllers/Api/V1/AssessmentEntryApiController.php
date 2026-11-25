<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;

use App\Domains\Attainment\Models\StudentAssessment;
use App\Domains\Attainment\Models\IndirectAssessment;

use App\Domains\Academic\Models\AcademicYear;

use App\Domains\Attainment\Services\DirectAttainmentService;
use App\Domains\SAR\Models\SarAuditLog;

class AssessmentEntryApiController extends Controller
{
    protected DirectAttainmentService $directService;

    public function __construct(DirectAttainmentService $directService)
    {
        $this->directService = $directService;
    }

    /**
     * Faculty records student marks batch for CIE or SEE
     */
    public function recordMarks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'course_outcome_id' => 'required|exists:course_outcomes,id',
            'assessment_type' => 'required|string|in:Internal_Test_1,Internal_Test_2,Assignment,End_Sem,CIE,SEE',
            'total_students_attempted' => 'required|integer|min:1',
            'students_meeting_target' => 'required|integer|min:0',
        ]);

        $year = AcademicYear::where('type', 'CAY')->first()
            ?? AcademicYear::first();

        $total = max(1, $validated['total_students_attempted']);
        $meeting = min($total, $validated['students_meeting_target']);
        $percentage = round(($meeting / $total) * 100, 2);

        if ($percentage >= 70.0) {
            $level = 3.0;
        } elseif ($percentage >= 60.0) {
            $level = 2.0;
        } elseif ($percentage >= 50.0) {
            $level = 1.0;
        } else {
            $level = 0.5;
        }

        $assessment = StudentAssessment::updateOrCreate(
            [
                'course_id' => $validated['course_id'],
                'course_outcome_id' => $validated['course_outcome_id'],
                'academic_year_id' => $year->id,
                'assessment_type' => $validated['assessment_type'],
            ],
            [
                'total_students_attempted' => $total,
                'students_meeting_target' => $meeting,
                'percentage_achieved' => $percentage,
                'attainment_level' => $level,
            ]
        );

        $co = CourseOutcome::find($validated['course_outcome_id']);

        $recomputed = $this->directService->computeCoDirectAttainment(
            $co,
            $year
        );

        SarAuditLog::create([
            'user_name' => auth()->user()?->name ?? 'Course Faculty',
            'user_role' => auth()->user()?->role ?? 'Faculty',
            'action' => 'MARKS_UPLOADED',
            'domain_module' => 'Attainment',
            'description' => "Logged {$validated['assessment_type']} marks for {$co->co_code}: {$percentage}% students met target (Level {$level}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Marks recorded for {$co->co_code}. Attainment Level: {$level}/3.0 ({$percentage}% students achieved target).",
            'data' => [
                'assessment' => $assessment,
                'recomputed_co' => $recomputed,
            ],
        ]);
    }

    /**
     * Get all survey records
     */
    public function surveys(Request $request): JsonResponse
    {
        $surveys = IndirectAssessment::with([
            'program',
            'academicYear',
        ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $surveys,
        ]);
    }

    /**
     * Record Survey Rating (Exit, Alumni, Employer)
     */
    public function recordSurvey(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'survey_type' => 'required|string|in:Graduate_Exit_Survey,Alumni_Survey,Employer_Survey',
            'target_type' => 'required|string|in:PO,PSO',
            'target_id' => 'required|integer',
            'average_rating' => 'required|numeric|between:1,3',
            'total_respondents' => 'nullable|integer|min:1',
        ]);

        $targetExists = $validated['target_type'] === 'PO'
            ? ProgramOutcome::where('program_id', $validated['program_id'])
                ->where('id', $validated['target_id'])
                ->exists()
            : ProgramSpecificOutcome::where('program_id', $validated['program_id'])
                ->where('id', $validated['target_id'])
                ->exists();

        if (!$targetExists) {
            return response()->json([
                'success' => false,
                'message' => 'The selected target does not belong to the selected program.',
            ], 422);
        }

        $year = AcademicYear::where('type', 'CAY')->first()
            ?? AcademicYear::first();

        $respondents = $validated['total_respondents'] ?? 50;

        $survey = IndirectAssessment::updateOrCreate(
            [
                'program_id' => $validated['program_id'],
                'academic_year_id' => $year->id,
                'survey_type' => $validated['survey_type'],
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
            ],
            [
                'average_rating' => $validated['average_rating'],
                'total_respondents' => $respondents,
            ]
        );

        SarAuditLog::create([
            'user_name' => auth()->user()?->name ?? 'Survey Administrator',
            'user_role' => auth()->user()?->role ?? 'Staff',
            'action' => 'SURVEY_RECORDED',
            'domain_module' => 'Attainment',
            'description' => "Recorded {$validated['survey_type']} rating for {$validated['target_type']}#{$validated['target_id']}: {$validated['average_rating']}/3.0 from {$respondents} respondents.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'payload' => [
                'survey_id' => $survey->id,
                'program_id' => $validated['program_id'],
                'survey_type' => $validated['survey_type'],
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
                'average_rating' => $validated['average_rating'],
                'total_respondents' => $respondents,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Survey rating updated for {$validated['target_type']}#{$validated['target_id']}.",
            'data' => $survey,
        ]);
    }

    /**
     * Update a survey record
     */
    public function updateSurvey(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'survey_type' => 'required|string|in:Graduate_Exit_Survey,Alumni_Survey,Employer_Survey',
            'target_type' => 'required|string|in:PO,PSO',
            'target_id' => 'required|integer',
            'average_rating' => 'required|numeric|between:1,3',
            'total_respondents' => 'nullable|integer|min:1',
        ]);

        $targetExists = $validated['target_type'] === 'PO'
            ? ProgramOutcome::where('program_id', $validated['program_id'])
                ->where('id', $validated['target_id'])
                ->exists()
            : ProgramSpecificOutcome::where('program_id', $validated['program_id'])
                ->where('id', $validated['target_id'])
                ->exists();

        if (!$targetExists) {
            return response()->json([
                'success' => false,
                'message' => 'The selected target does not belong to the selected program.',
            ], 422);
        }

        $survey = IndirectAssessment::findOrFail($id);

        $respondents = $validated['total_respondents'] ?? 50;

        $survey->update([
            'program_id' => $validated['program_id'],
            'survey_type' => $validated['survey_type'],
            'target_type' => $validated['target_type'],
            'target_id' => $validated['target_id'],
            'average_rating' => $validated['average_rating'],
            'total_respondents' => $respondents,
        ]);

        SarAuditLog::create([
            'user_name' => auth()->user()?->name ?? 'Survey Administrator',
            'user_role' => auth()->user()?->role ?? 'Staff',
            'action' => 'SURVEY_UPDATED',
            'domain_module' => 'Attainment',
            'description' => "Updated {$validated['survey_type']} rating for {$validated['target_type']}#{$validated['target_id']}: {$validated['average_rating']}/3.0 from {$respondents} respondents.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'payload' => [
                'survey_id' => $survey->id,
                'program_id' => $validated['program_id'],
                'survey_type' => $validated['survey_type'],
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
                'average_rating' => $validated['average_rating'],
                'total_respondents' => $respondents,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Survey record updated successfully.',
            'data' => $survey,
        ]);
    }

    /**
     * Delete a survey record
     */
    public function destroySurvey(int $id): JsonResponse
    {
        $survey = IndirectAssessment::findOrFail($id);

        SarAuditLog::create([
            'user_name' => auth()->user()?->name ?? 'Survey Administrator',
            'user_role' => auth()->user()?->role ?? 'Staff',
            'action' => 'SURVEY_DELETED',
            'domain_module' => 'Attainment',
            'description' => "Deleted {$survey->survey_type} rating for {$survey->target_type}#{$survey->target_id}.",
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'payload' => [
                'survey_id' => $survey->id,
                'program_id' => $survey->program_id,
                'survey_type' => $survey->survey_type,
                'target_type' => $survey->target_type,
                'target_id' => $survey->target_id,
                'average_rating' => $survey->average_rating,
                'total_respondents' => $survey->total_respondents,
            ],
        ]);

        $survey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Survey record deleted successfully.',
        ]);
    }

    /**
     * Get programs with their POs and PSOs for survey forms
     */
    public function surveyOptions(): JsonResponse
    {
        $programs = \App\Domains\Academic\Models\Program::with([
            'programOutcomes',
            'programSpecificOutcomes',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }
}
