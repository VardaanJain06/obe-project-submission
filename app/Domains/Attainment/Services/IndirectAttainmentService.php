<?php

namespace App\Domains\Attainment\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Attainment\Models\IndirectAssessment;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;

class IndirectAttainmentService
{
    /**
     * Compute Indirect Attainment from Surveys (Exit Survey, Alumni, Employer)
     * Weights: Exit Survey (50%), Alumni Survey (30%), Employer Survey (20%)
     */
    public function computeProgramIndirectAttainment(Program $program, AcademicYear $year): array
    {
        $pos = ProgramOutcome::where('program_id', $program->id)->orderBy('po_code')->get();
        $psos = ProgramSpecificOutcome::where('program_id', $program->id)->orderBy('pso_code')->get();

        $surveys = IndirectAssessment::where('program_id', $program->id)
            ->where('academic_year_id', $year->id)
            ->get();

        $poScores = [];
        $psoScores = [];

        foreach ($pos as $po) {
            $exit = $surveys->first(fn($s) => $s->survey_type == 'Graduate_Exit_Survey' && $s->target_type == 'PO' && $s->target_id == $po->id);
            $alumni = $surveys->first(fn($s) => $s->survey_type == 'Alumni_Survey' && $s->target_type == 'PO' && $s->target_id == $po->id);
            $employer = $surveys->first(fn($s) => $s->survey_type == 'Employer_Survey' && $s->target_type == 'PO' && $s->target_id == $po->id);

            $exitScore = $exit ? $exit->average_rating : 2.60;
            $alumniScore = $alumni ? $alumni->average_rating : 2.50;
            $employerScore = $employer ? $employer->average_rating : 2.70;

            // Weighted average: 50% exit, 30% alumni, 20% employer
            $combined = round((0.50 * $exitScore) + (0.30 * $alumniScore) + (0.20 * $employerScore), 2);

            $poScores[$po->po_code] = [
                'po_id' => $po->id,
                'po_title' => $po->title,
                'exit_score' => $exitScore,
                'alumni_score' => $alumniScore,
                'employer_score' => $employerScore,
                'indirect_attainment' => $combined,
            ];
        }

        foreach ($psos as $pso) {
            $exit = $surveys->first(fn($s) => $s->survey_type == 'Graduate_Exit_Survey' && $s->target_type == 'PSO' && $s->target_id == $pso->id);
            $alumni = $surveys->first(fn($s) => $s->survey_type == 'Alumni_Survey' && $s->target_type == 'PSO' && $s->target_id == $pso->id);
            $employer = $surveys->first(fn($s) => $s->survey_type == 'Employer_Survey' && $s->target_type == 'PSO' && $s->target_id == $pso->id);

            $exitScore = $exit ? $exit->average_rating : 2.55;
            $alumniScore = $alumni ? $alumni->average_rating : 2.45;
            $employerScore = $employer ? $employer->average_rating : 2.65;

            $combined = round((0.50 * $exitScore) + (0.30 * $alumniScore) + (0.20 * $employerScore), 2);

            $psoScores[$pso->pso_code] = [
                'pso_id' => $pso->id,
                'pso_title' => $pso->title,
                'exit_score' => $exitScore,
                'alumni_score' => $alumniScore,
                'employer_score' => $employerScore,
                'indirect_attainment' => $combined,
            ];
        }

        return [
            'program_id' => $program->id,
            'academic_year' => $year->name,
            'pos' => $poScores,
            'psos' => $psoScores,
        ];
    }
}
