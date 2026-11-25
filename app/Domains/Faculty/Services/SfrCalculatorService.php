<?php

namespace App\Domains\Faculty\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Academic\Models\AcademicIntake;
use App\Domains\Faculty\Models\FacultyAcademicYear;

class SfrCalculatorService
{
    /**
     * Calculate Student-Faculty Ratio (SFR) for a given program across CAY, CAYm1, CAYm2
     * Formula: SFR = N / F
     * N = UGT1 + UGT2 + UGT3
     * F = Regular faculty assigned to the program
     */
    public function calculate(Program $program, ?AcademicYear $cay = null): array
    {
        if (!$cay) {
            $cay = AcademicYear::where('type', 'CAY')->first() 
                ?? AcademicYear::orderByDesc('start_year')->first();
        }

        if (!$cay) {
            return $this->defaultResponse();
        }

        // Fetch CAY, CAYm1, CAYm2
        $years = AcademicYear::whereIn('type', ['CAY', 'CAYm1', 'CAYm2'])
            ->orderBy('start_year', 'asc')
            ->get();

        if ($years->isEmpty()) {
            $years = AcademicYear::orderBy('start_year', 'desc')->take(3)->get()->reverse();
        }

        $yearlyData = [];
        $totalN = 0;
        $totalF = 0;

        foreach ($years as $year) {
            // Student Intake: UGT1 + UGT2 + UGT3 (+ UGT4)
            $intake = AcademicIntake::where('program_id', $program->id)
                ->where('academic_year_id', $year->id)
                ->first();

            $ugt1 = $intake ? $intake->ug_1st_year : 0;
            $ugt2 = $intake ? $intake->ug_2nd_year : 0;
            $ugt3 = $intake ? $intake->ug_3rd_year : 0;
            $ugt4 = $intake ? $intake->ug_4th_year : 0;
            $nYear = $ugt1 + $ugt2 + $ugt3 + $ugt4; // Total enrolled UG students

            // Faculty count in this academic year (Regular / Contractual >= 1 yr)
            $fYear = FacultyAcademicYear::where('program_id', $program->id)
                ->where('academic_year_id', $year->id)
                ->whereIn('association_type', ['Regular', 'Contractual'])
                ->count();

            // Prevent division by zero
            $sfrYear = $fYear > 0 ? round($nYear / $fYear, 2) : 0;

            $yearlyData[$year->type ?? $year->name] = [
                'academic_year_id' => $year->id,
                'academic_year' => $year->name,
                'type' => $year->type,
                'ugt1' => $ugt1,
                'ugt2' => $ugt2,
                'ugt3' => $ugt3,
                'ugt4' => $ugt4,
                'total_students_N' => $nYear,
                'total_faculty_F' => $fYear,
                'sfr' => $sfrYear,
            ];

            $totalN += $nYear;
            $totalF += $fYear;
        }

        $avgN = count($years) > 0 ? round($totalN / count($years), 2) : 0;
        $avgF = count($years) > 0 ? round($totalF / count($years), 2) : 0;
        $avgSfr = $avgF > 0 ? round($avgN / $avgF, 2) : 0;

        // NBA Marks for SFR (Max Marks = 20 for Tier-II / Tier-I)
        // If SFR <= 15 => 20 marks
        // If 15 < SFR <= 20 => Marks = 20 * (15 / SFR) or pro-rata
        // If 20 < SFR <= 25 => Marks = 10 * (20 / SFR)
        // If SFR > 25 => 0 marks
        $claimedMarks = 0.0;
        $status = 'Standard';

        if ($avgSfr > 0 && $avgSfr <= 15.0) {
            $claimedMarks = 20.0;
            $status = 'Optimal (<= 15:1)';
        } elseif ($avgSfr > 15.0 && $avgSfr <= 20.0) {
            $claimedMarks = round(20.0 * (15.0 / $avgSfr), 2);
            $status = 'Acceptable (15:1 to 20:1)';
        } elseif ($avgSfr > 20.0 && $avgSfr <= 25.0) {
            $claimedMarks = round(15.0 * (15.0 / $avgSfr), 2);
            $status = 'Sub-optimal (20:1 to 25:1)';
        } else {
            $claimedMarks = 0.0;
            $status = 'High Deficiency (> 25:1)';
        }

        return [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'yearly_breakdown' => $yearlyData,
            'avg_students_N' => $avgN,
            'avg_faculty_F' => $avgF,
            'avg_sfr' => $avgSfr,
            'max_marks' => 20.0,
            'claimed_marks' => $claimedMarks,
            'status' => $status,
            'required_faculty_at_15' => ceil($avgN / 15),
            'required_faculty_at_20' => ceil($avgN / 20),
        ];
    }

    private function defaultResponse(): array
    {
        return [
            'program_id' => null,
            'program_name' => 'N/A',
            'yearly_breakdown' => [],
            'avg_students_N' => 0,
            'avg_faculty_F' => 0,
            'avg_sfr' => 0,
            'max_marks' => 20.0,
            'claimed_marks' => 0.0,
            'status' => 'Data Not Available',
            'required_faculty_at_15' => 0,
            'required_faculty_at_20' => 0,
        ];
    }
}
