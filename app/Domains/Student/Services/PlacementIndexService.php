<?php

namespace App\Domains\Student\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Student\Models\StudentPlacement;

class PlacementIndexService
{
    /**
     * Calculate Criterion 4.5: Placement, Higher Studies & Entrepreneurship Index
     * Assessment = 40 * Average Placement Index (P)
     * P = (x + y + z) / N
     * x = Placed students, y = Higher studies, z = Entrepreneurs, N = Final year students
     */
    public function calculate(Program $program): array
    {
        $placements = StudentPlacement::with('academicYear')
            ->where('program_id', $program->id)
            ->get();

        $yearlyList = [];
        $totalIndex = 0;

        foreach ($placements as $p) {
            $n = max(1, $p->final_year_students);
            $totalSuccess = $p->placed_students + $p->higher_studies_students + $p->entrepreneurs_count;
            $index = round($totalSuccess / $n, 2);

            $yearlyList[] = [
                'academic_year' => $p->academicYear->name ?? 'Year',
                'final_year_students_N' => $n,
                'placed_students_x' => $p->placed_students,
                'higher_studies_y' => $p->higher_studies_students,
                'entrepreneurs_z' => $p->entrepreneurs_count,
                'total_placed_or_higher' => $totalSuccess,
                'placement_index_P' => $index,
                'median_salary_lpa' => $p->median_salary_lpa,
            ];

            $totalIndex += $index;
        }

        $count = count($placements) > 0 ? count($placements) : 1;
        $avgIndex = round($totalIndex / $count, 2);
        // Max 40 marks
        $claimedMarks = min(40.0, round(40.0 * $avgIndex, 2));

        return [
            'program_id' => $program->id,
            'yearly_records' => $yearlyList,
            'avg_placement_index' => $avgIndex,
            'max_marks' => 40.0,
            'claimed_marks' => $claimedMarks,
        ];
    }
}
