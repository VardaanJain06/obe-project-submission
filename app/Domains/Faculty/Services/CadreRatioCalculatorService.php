<?php

namespace App\Domains\Faculty\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Faculty\Models\FacultyAcademicYear;

class CadreRatioCalculatorService
{
    /**
     * Calculate Faculty Cadre Proportion & Score (Criterion 5.2)
     * Standard Cadre Ratio = 1 : 2 : 6 (Professors : Assoc Profs : Asst Profs)
     * Cadre Proportion Score = [ (AF1 / RF1) + (AF2 / RF2 * 0.6) + (AF3 / RF3 * 0.4) ] * 12.5
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

        $yearlyCadre = [];
        $totalMarks = 0;

        foreach ($years as $year) {
            $faculty = FacultyAcademicYear::where('program_id', $program->id)
                ->where('academic_year_id', $year->id)
                ->whereIn('association_type', ['Regular', 'Contractual'])
                ->get();

            $professors = $faculty->filter(fn($f) => str_contains(strtolower($f->cadre ?? $f->designation), 'professor') && !str_contains(strtolower($f->cadre ?? $f->designation), 'associate') && !str_contains(strtolower($f->cadre ?? $f->designation), 'assistant'))->count();
            $assocProfs = $faculty->filter(fn($f) => str_contains(strtolower($f->cadre ?? $f->designation), 'associate'))->count();
            $asstProfs = $faculty->filter(fn($f) => str_contains(strtolower($f->cadre ?? $f->designation), 'assistant'))->count();

            $yearKey = $year->type ?? $year->name;
            $nStudents = $sfrData['yearly_breakdown'][$yearKey]['total_students_N'] ?? 0;
            $rf = max(1, ceil($nStudents / 20)); // Required regular faculty

            // Ideal Cadre Targets (1:2:6 ratio)
            $rfProf = max(1, round($rf / 9, 2));
            $rfAssoc = max(1, round(($rf / 9) * 2, 2));
            $rfAsst = max(1, round(($rf / 9) * 6, 2));

            // Cadre Proportion Score (Max 25 marks)
            $pScore = min(1.0, $professors / $rfProf);
            $aScore = min(1.0, $assocProfs / $rfAssoc);
            $asScore = min(1.0, $asstProfs / $rfAsst);

            $yearScore = round((($pScore * 1.0) + ($aScore * 0.7) + ($asScore * 0.4)) * (25 / 2.1), 2);
            $yearScoreCapped = min(25.0, $yearScore);

            $yearlyCadre[$yearKey] = [
                'academic_year' => $year->name,
                'type' => $year->type,
                'required_faculty_RF' => $rf,
                'actual_professors' => $professors,
                'required_professors' => $rfProf,
                'actual_assoc_profs' => $assocProfs,
                'required_assoc_profs' => $rfAssoc,
                'actual_asst_profs' => $asstProfs,
                'required_asst_profs' => $rfAsst,
                'score' => $yearScoreCapped,
            ];

            $totalMarks += $yearScoreCapped;
        }

        $avgMarks = count($years) > 0 ? round($totalMarks / count($years), 2) : 0;

        return [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'yearly_breakdown' => $yearlyCadre,
            'avg_score' => $avgMarks,
            'max_marks' => 25.0,
            'claimed_marks' => min(25.0, $avgMarks),
            'target_ratio' => '1 : 2 : 6 (Prof : Assoc Prof : Asst Prof)',
        ];
    }
}
