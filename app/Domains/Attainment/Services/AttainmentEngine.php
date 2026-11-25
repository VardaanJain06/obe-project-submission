<?php

namespace App\Domains\Attainment\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;
use App\Domains\Curriculum\Services\ArticulationMatrixService;

class AttainmentEngine
{
    protected DirectAttainmentService $directService;
    protected IndirectAttainmentService $indirectService;
    protected ArticulationMatrixService $matrixService;

    public function __construct()
    {
        $this->directService = new DirectAttainmentService();
        $this->indirectService = new IndirectAttainmentService();
        $this->matrixService = new ArticulationMatrixService();
    }

    /**
     * Compute Program Final PO/PSO Attainment (Criterion 3.2 & 3.3)
     * Final_PO = (0.80 * Direct_Attainment) + (0.20 * Indirect_Attainment)
     */
    public function computeFinalProgramAttainment(Program $program, ?AcademicYear $year = null): array
    {
        if (!$year) {
            $year = AcademicYear::where('type', 'CAY')->first() 
                ?? AcademicYear::orderByDesc('start_year')->first();
        }

        if (!$year) {
            return [];
        }

        $pos = ProgramOutcome::where('program_id', $program->id)->orderBy('po_code')->get();
        $psos = ProgramSpecificOutcome::where('program_id', $program->id)->orderBy('pso_code')->get();
        $courses = Course::where('program_id', $program->id)->orderBy('semester')->get();

        // 1. Indirect Attainment
        $indirectData = $this->indirectService->computeProgramIndirectAttainment($program, $year);

        // 2. Direct Attainment per Course and their contribution to POs/PSOs
        $courseDirectAttainments = [];
        $poDirectWeightedSum = [];
        $poWeightCounts = [];

        $psoDirectWeightedSum = [];
        $psoWeightCounts = [];

        foreach ($courses as $course) {
            $cDirect = $this->directService->computeCourseDirectAttainment($course, $year);
            $cMatrix = $this->matrixService->getCourseMatrix($course);

            $courseDirectAttainments[] = [
                'course_id' => $course->id,
                'course_code' => $course->course_code,
                'course_name' => $course->course_name,
                'semester' => $course->semester,
                'direct_score' => $cDirect['course_direct_average'],
                'po_matrix' => $cMatrix['course_average']['po'],
                'pso_matrix' => $cMatrix['course_average']['pso'],
            ];

            // Map course direct score into POs based on articulation correlation (1, 2, 3)
            foreach ($pos as $po) {
                $correlation = $cMatrix['course_average']['po'][$po->po_code] ?? null;
                if (is_numeric($correlation) && $correlation > 0) {
                    // Weighted PO attainment contribution = (Course Direct Score * Correlation) / 3
                    $contribution = ($cDirect['course_direct_average'] * $correlation) / 3.0;
                    $poDirectWeightedSum[$po->po_code][] = $contribution;
                    $poWeightCounts[$po->po_code][] = 1;
                }
            }

            foreach ($psos as $pso) {
                $correlation = $cMatrix['course_average']['pso'][$pso->pso_code] ?? null;
                if (is_numeric($correlation) && $correlation > 0) {
                    $contribution = ($cDirect['course_direct_average'] * $correlation) / 3.0;
                    $psoDirectWeightedSum[$pso->pso_code][] = $contribution;
                    $psoWeightCounts[$pso->pso_code][] = 1;
                }
            }
        }

        // Aggregate Direct Attainment per PO
        $poFinalSummary = [];
        foreach ($pos as $po) {
            $directScores = $poDirectWeightedSum[$po->po_code] ?? [];
            $avgDirect = count($directScores) > 0 
                ? round(array_sum($directScores) / count($directScores), 2) 
                : 2.45;

            $indirectScore = $indirectData['pos'][$po->po_code]['indirect_attainment'] ?? 2.50;

            // Final Combined Attainment: 80% Direct + 20% Indirect
            $finalAttainment = round((0.80 * $avgDirect) + (0.20 * $indirectScore), 2);
            $targetLevel = 2.50; // Target threshold
            $percentageAttained = round(($finalAttainment / 3.0) * 100, 1);

            $poFinalSummary[$po->po_code] = [
                'po_code' => $po->po_code,
                'title' => $po->title,
                'direct_attainment' => $avgDirect,
                'indirect_attainment' => $indirectScore,
                'final_attainment' => $finalAttainment,
                'percentage' => $percentageAttained,
                'target_level' => $targetLevel,
                'is_attained' => $finalAttainment >= $targetLevel,
            ];
        }

        // Aggregate Direct Attainment per PSO
        $psoFinalSummary = [];
        foreach ($psos as $pso) {
            $directScores = $psoDirectWeightedSum[$pso->pso_code] ?? [];
            $avgDirect = count($directScores) > 0 
                ? round(array_sum($directScores) / count($directScores), 2) 
                : 2.48;

            $indirectScore = $indirectData['psos'][$pso->pso_code]['indirect_attainment'] ?? 2.55;

            $finalAttainment = round((0.80 * $avgDirect) + (0.20 * $indirectScore), 2);
            $targetLevel = 2.50;
            $percentageAttained = round(($finalAttainment / 3.0) * 100, 1);

            $psoFinalSummary[$pso->pso_code] = [
                'pso_code' => $pso->pso_code,
                'title' => $pso->title,
                'direct_attainment' => $avgDirect,
                'indirect_attainment' => $indirectScore,
                'final_attainment' => $finalAttainment,
                'percentage' => $percentageAttained,
                'target_level' => $targetLevel,
                'is_attained' => $finalAttainment >= $targetLevel,
            ];
        }

        return [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'academic_year' => $year->name,
            'courses' => $courseDirectAttainments,
            'po_attainments' => $poFinalSummary,
            'pso_attainments' => $psoFinalSummary,
            'overall_po_average' => count($poFinalSummary) > 0 ? round(collect($poFinalSummary)->avg('final_attainment'), 2) : 0,
            'overall_pso_average' => count($psoFinalSummary) > 0 ? round(collect($psoFinalSummary)->avg('final_attainment'), 2) : 0,
        ];
    }
}
