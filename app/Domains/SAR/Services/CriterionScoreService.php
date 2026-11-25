<?php

namespace App\Domains\SAR\Services;

use App\Domains\SAR\Models\SarReport;
use App\Domains\Academic\Models\AcademicIntake;
use App\Domains\Faculty\Services\SfrCalculatorService;
use App\Domains\Faculty\Services\FqiCalculatorService;
use App\Domains\Faculty\Services\CadreRatioCalculatorService;
use App\Domains\Attainment\Services\AttainmentEngine;
use App\Domains\Student\Services\SuccessRateCalculatorService;
use App\Domains\Student\Services\PlacementIndexService;
use App\Domains\Finance\Services\BudgetAnalysisService;

class CriterionScoreService
{
    protected SfrCalculatorService $sfrService;
    protected FqiCalculatorService $fqiService;
    protected CadreRatioCalculatorService $cadreService;
    protected AttainmentEngine $attainmentEngine;
    protected SuccessRateCalculatorService $successService;
    protected PlacementIndexService $placementService;
    protected BudgetAnalysisService $budgetService;

    public function __construct()
    {
        $this->sfrService = new SfrCalculatorService();
        $this->fqiService = new FqiCalculatorService();
        $this->cadreService = new CadreRatioCalculatorService();
        $this->attainmentEngine = new AttainmentEngine();
        $this->successService = new SuccessRateCalculatorService();
        $this->placementService = new PlacementIndexService();
        $this->budgetService = new BudgetAnalysisService();
    }

    /**
     * Calculate current scores for all 10 NBA criteria.
     */
    public function calculate(SarReport $report): array
    {
        $program = $report->program;
        $cay = $report->academicYear;

        // Existing live calculation engines
        $sfrData = $this->sfrService->calculate($program, $cay);
        $fqiData = $this->fqiService->calculate($program, $cay);
        $cadreData = $this->cadreService->calculate($program, $cay);

        $attainmentData = $this->attainmentEngine
            ->computeFinalProgramAttainment($program, $cay);

        $successData = $this->successService->calculate($program);
        $placementData = $this->placementService->calculate($program);
        $budgetData = $this->budgetService->analyze($program);

        /*
         * Criterion 1
         *
         * No dedicated live calculation engine currently exists.
         * Keep the existing compiler value until a dedicated
         * Criterion 1 data source/service is implemented.
         */
        $c1Marks = 54.0;

        /*
         * Criterion 2
         *
         * Use live AcademicIntake data for the selected program.
         *
         * The repository does not currently define a dedicated
         * Criterion 2 marks formula, so the existing 108/120
         * marks are preserved for now. The intake metrics below
         * are calculated from the live database and exposed in
         * the criterion summary.
         */
        $intakes = AcademicIntake::with('academicYear')
            ->where('program_id', $program->id)
            ->get();

        $currentIntake = $intakes->firstWhere(
            'academic_year_id',
            $cay->id
        );

        $currentTotalStudents = (int) (
            $currentIntake?->total_students ?? 0
        );

        $averageIntake = (float) $intakes->avg(function ($intake) {
            return (int) ($intake->total_students ?? 0);
        });

        $intakeStability = $averageIntake > 0
            ? round(
                ($currentTotalStudents / $averageIntake) * 100,
                1
            )
            : 0.0;

        /*
         * Preserve the existing score until the project-specific
         * Criterion 2 marks mapping is defined.
         */
        $c2Marks = 108.0;

        /*
         * Criterion 3
         *
         * PO attainment is calculated from the live attainment engine.
         */
        $coPoAttainmentAvg = $attainmentData['overall_po_average'] ?? 0;

        $c3Marks = round(
            120.0 * ($coPoAttainmentAvg / 3.0),
            1
        );

        $c3Marks = min(
            120.0,
            max(0.0, $c3Marks)
        );

        /*
         * Criterion 4
         *
         * Uses existing Success Rate + Placement engines.
         */
        $successMarks = $successData['total_success_marks'] ?? 0;
        $placementMarks = $placementData['claimed_marks'] ?? 0;
        $academicPerformance = 62.0;

        $c4Marks = min(
            150.0,
            round(
                $successMarks +
                $placementMarks +
                $academicPerformance,
                1
            )
        );

        /*
         * Criterion 5
         *
         * Uses existing SFR + FQI + Cadre engines.
         * Other components remain based on the existing compiler
         * until dedicated engines/data sources exist.
         */
        $sfrScore = $sfrData['claimed_marks'] ?? 0;
        $cadreScore = $cadreData['claimed_marks'] ?? 0;
        $fqiScore = $fqiData['claimed_marks'] ?? 0;

        $facultyRetention = 23.0;
        $fdpScore = 26.0;
        $researchScore = 27.5;
        $developmentScore = 38.0;

        $c5Marks = min(
            200.0,
            round(
                $sfrScore +
                $cadreScore +
                $fqiScore +
                $facultyRetention +
                $fdpScore +
                $researchScore +
                $developmentScore,
                1
            )
        );

        /*
         * Criteria 6-9
         *
         * Dedicated calculation engines/data sources are not currently
         * available in the inspected codebase, so preserve the existing
         * values rather than inventing new calculations.
         */
        $c6Marks = 72.0;
        $c7Marks = 43.0;
        $c8Marks = 44.0;
        $c9Marks = 45.0;

        /*
         * Criterion 10
         *
         * Budget component is calculated live.
         */
        $budgetScore = $budgetData['claimed_marks'] ?? 0;
        $governanceScore = 80.0;

        $c10Marks = min(
            120.0,
            round(
                $budgetScore +
                $governanceScore,
                1
            )
        );

        $c1Summary = 'Vision: At TIET, our vision rests on two pillars: (1) A deeply meaningful experience for all stakeholders that shapes lives with wisdom, purpose, and compassion; and (2) Socially impactful research that advances knowledge while solving real challenges of industry, society, and humanity. Together, they embody our commitment to seeking excellence for all. | Mission: TIET exists to educate and empower students while advancing knowledge through research, so that we serve our society with wisdom, compassion, and a commitment to seek excellence.';

        $criteria = [
            $this->criterion(
                1,
                'Vision, Mission and Program Educational Objectives',
                60.0,
                $c1Marks,
                $c1Summary
            ),

            $this->criterion(
                2,
                'Program Curriculum and Teaching-Learning Processes',
                120.0,
                $c2Marks,
                "Current intake: {$currentTotalStudents}, " .
                "3-year average: {$averageIntake}, " .
                "intake stability: {$intakeStability}%"
            ),

            $this->criterion(
                3,
                'Course Outcomes and Program Outcomes',
                120.0,
                $c3Marks,
                "PO attainment average: {$coPoAttainmentAvg}/3.0"
            ),

            $this->criterion(
                4,
                "Students' Performance and Placement",
                150.0,
                $c4Marks,
                "Success Rate: " .
                ($successData['mean_si_without_backlog'] ?? 0) .
                ", Placement Index: " .
                ($placementData['avg_placement_index'] ?? 0)
            ),

            $this->criterion(
                5,
                'Faculty Information and Contributions',
                200.0,
                $c5Marks,
                "Average SFR: " .
                ($sfrData['avg_sfr'] ?? 0) .
                ":1, Average FQI: " .
                ($fqiData['avg_fqi'] ?? 0) .
                "/25.0"
            ),

            $this->criterion(
                6,
                'Facilities and Technical Support',
                80.0,
                $c6Marks,
                'Facilities and technical support'
            ),

            $this->criterion(
                7,
                'Continuous Improvement',
                50.0,
                $c7Marks,
                'Continuous improvement initiatives'
            ),

            $this->criterion(
                8,
                'First Year Academics',
                50.0,
                $c8Marks,
                'First year academic support'
            ),

            $this->criterion(
                9,
                'Student Support Systems',
                50.0,
                $c9Marks,
                'Student support systems'
            ),

            $this->criterion(
                10,
                'Governance, Institutional Support & Financial Resources',
                120.0,
                $c10Marks,
                'Budget and governance'
            ),
        ];

        $totalClaimed = round(
            array_sum(
                array_column($criteria, 'claimed_marks')
            ),
            1
        );

        $totalMax = 1000.0;

        return [
            'report_id' => $report->id,

            'academic_year' => $cay->name ?? null,

            'total_claimed_marks' => $totalClaimed,

            'total_max_marks' => $totalMax,

            'total_percentage' => round(
                ($totalClaimed / $totalMax) * 100,
                1
            ),

            'criteria' => $criteria,

            // Useful for the Criteria Explorer's existing metric cards.
            'live_engines' => [
                'sfr' => $sfrData,
                'fqi' => $fqiData,
                'cadre' => $cadreData,
                'attainment' => $attainmentData,
                'success_rate' => $successData,
                'placement' => $placementData,
                'budget' => $budgetData,

                // Live Criterion 2 intake metrics
                'intake' => [
                    'current_total_students' => $currentTotalStudents,
                    'average_total_students' => round(
                        $averageIntake,
                        1
                    ),
                    'stability_percentage' => $intakeStability,
                ],
            ],
        ];
    }

    /**
     * Format one criterion consistently.
     */
    protected function criterion(
        int $number,
        string $title,
        float $maxMarks,
        float $claimedMarks,
        string $summary
    ): array {
        $claimedMarks = round(
            min(
                $maxMarks,
                max(0.0, $claimedMarks)
            ),
            1
        );

        return [
            'number' => $number,
            'title' => $title,
            'max_marks' => $maxMarks,
            'claimed_marks' => $claimedMarks,
            'percentage' => $maxMarks > 0
                ? round(
                    ($claimedMarks / $maxMarks) * 100,
                    1
                )
                : 0,
            'summary' => $summary,
        ];
    }
}