<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Faculty\Models\FacultyProfile;
use App\Domains\Faculty\Services\SfrCalculatorService;

class SfrCalculatorApiController extends Controller
{
    protected SfrCalculatorService $sfrService;

    public function __construct(SfrCalculatorService $sfrService)
    {
        $this->sfrService = $sfrService;
    }

    /**
     * Get SFR metrics for a program
     */
    public function getMetrics(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();

        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Program not found'], 404);
        }

        $result = $this->sfrService->calculate($program);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Live Simulator for SFR based on user inputs
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ugt1' => 'required|numeric|min:0',
            'ugt2' => 'required|numeric|min:0',
            'ugt3' => 'required|numeric|min:0',
            'ugt4' => 'nullable|numeric|min:0',
            'faculty_count' => 'required|numeric|min:1',
        ]);

        $totalStudents = $validated['ugt1'] + $validated['ugt2'] + $validated['ugt3'] + ($validated['ugt4'] ?? 0);
        $faculty = max(1, $validated['faculty_count']);
        $sfr = round($totalStudents / $faculty, 2);

        $claimedMarks = 0.0;
        $status = 'Standard';

        if ($sfr <= 15.0) {
            $claimedMarks = 20.0;
            $status = 'Optimal (<= 15:1) - Full 20 Marks';
        } elseif ($sfr <= 20.0) {
            $claimedMarks = round(20.0 * (15.0 / $sfr), 2);
            $status = 'Acceptable (15:1 to 20:1)';
        } elseif ($sfr <= 25.0) {
            $claimedMarks = round(15.0 * (15.0 / $sfr), 2);
            $status = 'Sub-optimal (20:1 to 25:1)';
        } else {
            $claimedMarks = 0.0;
            $status = 'Deficient (> 25:1) - Zero Marks';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_students' => $totalStudents,
                'faculty_count' => $faculty,
                'simulated_sfr' => $sfr,
                'claimed_marks' => $claimedMarks,
                'max_marks' => 20.0,
                'status' => $status,
                'required_faculty_at_15' => ceil($totalStudents / 15),
                'required_faculty_at_20' => ceil($totalStudents / 20),
            ],
        ]);
    }

    /**
     * Get Table 5A: Faculty Information Ledger for Criterion 5
     */
    public function getTable5a(Request $request): JsonResponse
    {
        $departmentId = $request->query('department_id', 1);

        $profiles = FacultyProfile::with(['academicYears' => function ($q) {
            $q->latest();
        }])
        ->where('department_id', $departmentId)
        ->orderBy('id', 'asc')
        ->get();

        $tableData = $profiles->map(function ($p, $index) {
            $latestAy = $p->academicYears->first();
            $presDesig = $p->present_designation ?? ($latestAy?->designation ?? 'Assistant Professor');

            return [
                'serial_no' => $index + 1,
                'name' => $p->name,
                'apaar_id' => $p->apaar_id ?? '',
                'highest_degree' => $p->highest_degree,
                'university' => $p->university_awarded ?? 'Not Available',
                'specialization' => $p->degree_specialization ?? 'Not Available',
                'date_of_joining' => $p->joining_date?->format('j F Y') ?? 'Not Available',
                'experience' => $p->experience_display ?? "{$p->teaching_experience_years} years",
                'designation_at_joining' => $p->designation_at_joining ?? 'Assistant Professor',
                'present_designation' => $presDesig,
                'date_designated_prof_assoc' => $p->date_designated_prof_assoc ?? '',
                'nature_of_association' => $latestAy?->association_type ?? 'Regular',
                'contractual_type' => $p->contractual_type ?? 'FT',
                'currently_associated' => $p->currently_associated ?? ($p->is_active ? 'Yes' : 'No'),
                'date_of_leaving' => $p->date_of_leaving ?? '',
            ];
        });

        $profCount = $tableData->filter(function ($x) {
            $d = strtolower($x['present_designation']);
            return (str_contains($d, 'professor') || str_contains($d, 'dean'))
                && !str_contains($d, 'associate')
                && !str_contains($d, 'assistant')
                && !str_contains($d, 'assoc')
                && !str_contains($d, 'asst');
        })->count();

        $assocCount = $tableData->filter(function ($x) {
            $d = strtolower($x['present_designation']);
            return str_contains($d, 'associate') || str_contains($d, 'assoc');
        })->count();

        $asstCount = $tableData->filter(function ($x) {
            $d = strtolower($x['present_designation']);
            return str_contains($d, 'assistant') || str_contains($d, 'asst') || str_contains($d, 'ap-') || str_contains($d, 'lecturer');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'faculty' => $tableData,
                'total_count' => $tableData->count(),
                'cadre_distribution' => [
                    'professors' => $profCount,
                    'associate_professors' => $assocCount,
                    'assistant_professors' => $asstCount,
                    'other' => $tableData->count() - ($profCount + $assocCount + $asstCount),
                    'ratio' => "{$profCount} : {$assocCount} : {$asstCount}",
                ],
            ],
        ]);
    }
}
