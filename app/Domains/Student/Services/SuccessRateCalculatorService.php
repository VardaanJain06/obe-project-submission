<?php

namespace App\Domains\Student\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Student\Models\StudentCohort;
use App\Domains\Student\Models\StudentPlacement;

class SuccessRateCalculatorService
{
    /**
     * Calculate Criterion 4.1 & 4.2: Success Rate without Backlogs & with Backlogs
     * Success Index (SI) = Number of students graduated in stipulated period / Total admitted students
     * Marks without backlog = 25 * Average SI
     * Marks with backlog = 15 * Average SI
     */
    public function calculate(Program $program): array
    {
        $cohorts = StudentCohort::where('program_id', $program->id)
            ->orderBy('entry_year', 'asc')
            ->get();

        $cohortList = [];
        $totalSiWithoutBacklog = 0;
        $totalSiInStipulatedTime = 0;

        foreach ($cohorts as $cohort) {
            $totalAdmitted = $cohort->admitted_first_year + $cohort->lateral_admitted_second_year;
            $totalAdmitted = max(1, $totalAdmitted);

            $siWithoutBacklog = round($cohort->graduated_without_backlog / $totalAdmitted, 2);
            $siInStipulatedTime = round($cohort->graduated_in_stipulated_time / $totalAdmitted, 2);

            $cohortList[] = [
                'batch_name' => $cohort->batch_name,
                'entry_year' => $cohort->entry_year,
                'graduation_year' => $cohort->graduation_year,
                'sanctioned_intake' => $cohort->sanctioned_intake,
                'admitted_first_year' => $cohort->admitted_first_year,
                'lateral_admitted' => $cohort->lateral_admitted_second_year,
                'total_admitted' => $totalAdmitted,
                'graduated_without_backlog' => $cohort->graduated_without_backlog,
                'si_without_backlog' => $siWithoutBacklog,
                'graduated_in_stipulated_time' => $cohort->graduated_in_stipulated_time,
                'si_stipulated_time' => $siInStipulatedTime,
            ];

            $totalSiWithoutBacklog += $siWithoutBacklog;
            $totalSiInStipulatedTime += $siInStipulatedTime;
        }

        $count = count($cohorts) > 0 ? count($cohorts) : 1;
        $meanSiWithoutBacklog = round($totalSiWithoutBacklog / $count, 2);
        $meanSiStipulatedTime = round($totalSiInStipulatedTime / $count, 2);

        // Claimed marks: Criterion 4.1 (Max 25), Criterion 4.2 (Max 15)
        $marksWithoutBacklog = min(25.0, round(25.0 * $meanSiWithoutBacklog, 2));
        $marksStipulatedTime = min(15.0, round(15.0 * $meanSiStipulatedTime, 2));

        return [
            'program_id' => $program->id,
            'cohorts' => $cohortList,
            'mean_si_without_backlog' => $meanSiWithoutBacklog,
            'marks_without_backlog' => $marksWithoutBacklog, // Max 25
            'mean_si_stipulated_time' => $meanSiStipulatedTime,
            'marks_stipulated_time' => $marksStipulatedTime, // Max 15
            'total_success_marks' => round($marksWithoutBacklog + $marksStipulatedTime, 2), // Max 40
        ];
    }
}
