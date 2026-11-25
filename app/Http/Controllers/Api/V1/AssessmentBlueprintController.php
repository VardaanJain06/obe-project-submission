<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssessmentBatch;
use App\Models\AssessmentQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentBlueprintController extends Controller
{
    /**
     * Create an assessment batch with its question → CO blueprint.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'assessment_type' => 'required|string|in:Internal_Test_1,Internal_Test_2,Assignment,End_Sem,CIE,SEE',
            'assessment_name' => 'required|string|max:255',
            'total_marks' => 'required|numeric|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question_number' => 'required|integer|min:1',
            'questions.*.course_outcome_id' => 'required|exists:course_outcomes,id',
            'questions.*.max_marks' => 'required|numeric|min:0.01',
        ]);

        $batch = DB::transaction(function () use ($validated) {
            $batch = AssessmentBatch::create([
                'course_id' => $validated['course_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'assessment_type' => $validated['assessment_type'],
                'assessment_name' => $validated['assessment_name'],
                'total_marks' => $validated['total_marks'],
            ]);

            foreach ($validated['questions'] as $question) {
                AssessmentQuestion::create([
                    'assessment_batch_id' => $batch->id,
                    'question_number' => $question['question_number'],
                    'course_outcome_id' => $question['course_outcome_id'],
                    'max_marks' => $question['max_marks'],
                ]);
            }

            return $batch;
        });

        return response()->json([
            'success' => true,
            'message' => 'Assessment blueprint created successfully.',
            'data' => $batch->load('questions.courseOutcome'),
        ], 201);
    }

    /**
     * Get an existing assessment blueprint.
     */
    public function show(AssessmentBatch $assessmentBatch): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $assessmentBatch->load([
                'course',
                'academicYear',
                'questions.courseOutcome',
            ]),
        ]);
    }
}