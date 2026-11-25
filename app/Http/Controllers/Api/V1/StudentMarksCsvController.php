<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssessmentBatch;
use App\Models\AssessmentQuestion;
use App\Models\StudentMark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentMarksCsvController extends Controller
{
    /**
     * Upload a CSV containing student marks.
     *
     * Expected format:
     * student_id,Q1,Q2,Q3...
     */
    public function upload(Request $request, AssessmentBatch $assessmentBatch): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120',
            ],
        ]);

        $file = $request->file('file');

        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to read the uploaded CSV file.',
            ], 422);
        }

        $header = fgetcsv($handle);

        if (!$header || count($header) < 2) {
            fclose($handle);

            return response()->json([
                'success' => false,
                'message' => 'CSV must contain student_id and at least one question.',
            ], 422);
        }

        $header = array_map(
            fn ($value) => strtolower(trim((string) $value)),
            $header
        );

        if ($header[0] !== 'student_id') {
            fclose($handle);

            return response()->json([
                'success' => false,
                'message' => 'First CSV column must be student_id.',
            ], 422);
        }

        $questions = $assessmentBatch->questions()
            ->orderBy('question_number')
            ->get();

        if ($questions->isEmpty()) {
            fclose($handle);

            return response()->json([
                'success' => false,
                'message' => 'No questions have been configured for this assessment.',
            ], 422);
        }

        $questionMap = [];

        foreach ($questions as $question) {
            $questionMap['q' . $question->question_number] = $question;
        }

        $csvQuestions = array_slice($header, 1);

        foreach ($csvQuestions as $csvQuestion) {
            if (!isset($questionMap[$csvQuestion])) {
                fclose($handle);

                return response()->json([
                    'success' => false,
                    'message' => "Question {$csvQuestion} is not configured for this assessment.",
                ], 422);
            }
        }

        $rows = [];
        $errors = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($data) === 1 && trim((string) $data[0]) === '') {
                continue;
            }

            if (count($data) !== count($header)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => 'Number of columns does not match the header.',
                ];

                continue;
            }

            $studentId = trim((string) $data[0]);

            if ($studentId === '') {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => 'Student ID cannot be empty.',
                ];

                continue;
            }

            foreach ($csvQuestions as $index => $questionCode) {
                $value = trim((string) $data[$index + 1]);
                $question = $questionMap[$questionCode];

                if ($value === '' || !is_numeric($value)) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'student_id' => $studentId,
                        'question' => strtoupper($questionCode),
                        'message' => 'Marks must be numeric.',
                    ];

                    continue;
                }

                $marks = (float) $value;

                if ($marks < 0 || $marks > $question->max_marks) {
                    $errors[] = [
                        'row' => $rowNumber,
                        'student_id' => $studentId,
                        'question' => strtoupper($questionCode),
                        'message' => "Marks must be between 0 and {$question->max_marks}.",
                    ];

                    continue;
                }

                $rows[] = [
                    'assessment_question_id' => $question->id,
                    'student_identifier' => $studentId,
                    'marks_obtained' => $marks,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        fclose($handle);

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'CSV validation failed. No marks were imported.',
                'errors' => $errors,
            ], 422);
        }

        if (empty($rows)) {
            return response()->json([
                'success' => false,
                'message' => 'CSV contains no valid student marks.',
            ], 422);
        }

        DB::transaction(function () use ($rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                StudentMark::upsert(
                    $chunk,
                    [
                        'assessment_question_id',
                        'student_identifier',
                    ],
                    [
                        'marks_obtained',
                        'updated_at',
                    ]
                );
            }
        });

        $studentCount = collect($rows)
            ->pluck('student_identifier')
            ->unique()
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Student marks imported successfully.',
            'data' => [
                'students_imported' => $studentCount,
                'marks_imported' => count($rows),
                'assessment_batch_id' => $assessmentBatch->id,
            ],
        ]);
    }
}