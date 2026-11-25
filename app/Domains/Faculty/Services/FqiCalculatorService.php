<?php

namespace App\Domains\Faculty\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Faculty\Models\FacultyAcademicYear;

class FqiCalculatorService
{
    /**
     * Calculate Faculty Qualification Index (FQI)
     * Formula: FQI = 2.5 * [(10X + 4Y) / RF]
     * X = Regular faculty with Ph.D.
     * Y = Regular faculty with M.Tech/M.E.
     * RF = Required Faculty based on standard SFR (1:20) = N / 20
     */
    public function calculate(Program $program, ?AcademicYear $cay = null): array
    {
        $sfrService = new SfrCalculatorService();
        $sfrData = $sfrService->calculate($program, $cay);

        $years = AcademicYear::whereIn('type', ['CAY', 'CAYm1', 'CAYm2'])
            ->orderBy('start_year', 'asc')
            ->get();

        if ($years->isEmpty()) {
            $years = AcademicYear::orderBy('start_year', 'desc')->take(3)->get()->reverse();
        }

        $yearlyFqi = [];
        $totalFqi = 0;

        foreach ($years as $year) {
            $facultyRecords = FacultyAcademicYear::with('facultyProfile')
                ->where('program_id', $program->id)
                ->where('academic_year_id', $year->id)
                ->whereIn('association_type', ['Regular', 'Contractual'])
                ->get();

            $phdCount = 0;
            $mtechCount = 0;
            $btechCount = 0;

            foreach ($facultyRecords as $fa) {
                $degree = strtoupper(trim($fa->facultyProfile->highest_degree ?? ''));
                if (str_contains($degree, 'PH.D') || str_contains($degree, 'PH. D') || str_contains($degree, 'PHD') || str_contains($degree, 'POSTDOC') || str_contains($degree, 'DOCTOR')) {
                    $phdCount++;
                } elseif (str_contains($degree, 'M.TECH') || str_contains($degree, 'ME') || str_contains($degree, 'M.E.') || str_contains($degree, 'MS') || str_contains($degree, 'M.S.')) {
                    $mtechCount++;
                } else {
                    $btechCount++;
                }
            }

            // Student count for this year to derive RF (Required Faculty at 1:20)
            $yearKey = $year->type ?? $year->name;
            $nStudents = $sfrData['yearly_breakdown'][$yearKey]['total_students_N'] ?? 0;
            $rf = max(1, ceil($nStudents / 20)); // Minimum 1 to avoid / 0

            // FQI = 2.5 * [(10*X + 4*Y) / RF]
            $fqiScore = round(2.5 * ((10 * $phdCount + 4 * $mtechCount) / $rf), 2);
            // Cap at 25 marks (max for this sub-criterion)
            $fqiScoreCapped = min(25.0, $fqiScore);

            $yearlyFqi[$yearKey] = [
                'academic_year' => $year->name,
                'type' => $year->type,
                'total_faculty' => $facultyRecords->count(),
                'phd_count_X' => $phdCount,
                'mtech_count_Y' => $mtechCount,
                'other_count' => $btechCount,
                'required_faculty_RF' => $rf,
                'fqi_raw' => $fqiScore,
                'fqi_capped' => $fqiScoreCapped,
            ];

            $totalFqi += $fqiScoreCapped;
        }

        $avgFqi = count($years) > 0 ? round($totalFqi / count($years), 2) : 0;

        return [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'yearly_breakdown' => $yearlyFqi,
            'avg_fqi' => $avgFqi,
            'max_marks' => 25.0,
            'claimed_marks' => min(25.0, $avgFqi),
            'benchmark' => 'Target FQI >= 20.0 for full credit',
        ];
    }
}
