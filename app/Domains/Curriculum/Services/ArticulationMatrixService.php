<?php

namespace App\Domains\Curriculum\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;
use App\Domains\Curriculum\Models\CoPoMapping;

class ArticulationMatrixService
{
    /**
     * Generate Course-level and Program-level Articulation Matrix
     */
    public function getCourseMatrix(Course $course): array
    {
        $program = $course->program;
        $pos = ProgramOutcome::where('program_id', $program->id)->orderBy('po_code')->get();
        $psos = ProgramSpecificOutcome::where('program_id', $program->id)->orderBy('pso_code')->get();
        $cos = $course->courseOutcomes()->orderBy('co_code')->get();

        $matrix = [];
        $coIds = $cos->pluck('id');
        $mappings = CoPoMapping::whereIn('course_outcome_id', $coIds)->get();

        $poAverages = [];
        $psoAverages = [];

        foreach ($cos as $co) {
            $row = [
                'co_id' => $co->id,
                'co_code' => $co->co_code,
                'description' => $co->description,
                'bloom_level' => $co->bloom_level,
                'target_percentage' => $co->target_percentage,
                'po_mappings' => [],
                'pso_mappings' => [],
            ];

            foreach ($pos as $po) {
                $m = $mappings->first(fn($map) => $map->course_outcome_id == $co->id && $map->target_type == 'PO' && $map->target_id == $po->id);
                $level = $m ? $m->correlation_level : null;
                $row['po_mappings'][$po->po_code] = [
                    'po_id' => $po->id,
                    'level' => $level,
                    'justification' => $m->justification ?? '',
                ];

                if ($level) {
                    $poAverages[$po->po_code][] = $level;
                }
            }

            foreach ($psos as $pso) {
                $m = $mappings->first(fn($map) => $map->course_outcome_id == $co->id && $map->target_type == 'PSO' && $map->target_id == $pso->id);
                $level = $m ? $m->correlation_level : null;
                $row['pso_mappings'][$pso->pso_code] = [
                    'pso_id' => $pso->id,
                    'level' => $level,
                    'justification' => $m->justification ?? '',
                ];

                if ($level) {
                    $psoAverages[$pso->pso_code][] = $level;
                }
            }

            $matrix[] = $row;
        }

        // Calculate Average Course Articulation Correlation per PO/PSO
        $summary = ['po' => [], 'pso' => []];
        foreach ($pos as $po) {
            $vals = $poAverages[$po->po_code] ?? [];
            $summary['po'][$po->po_code] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : '-';
        }
        foreach ($psos as $pso) {
            $vals = $psoAverages[$pso->pso_code] ?? [];
            $summary['pso'][$pso->pso_code] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : '-';
        }

        return [
            'course' => [
                'id' => $course->id,
                'course_code' => $course->course_code,
                'course_name' => $course->course_name,
                'semester' => $course->semester,
                'credits' => $course->credits,
            ],
            'pos' => $pos,
            'psos' => $psos,
            'matrix' => $matrix,
            'course_average' => $summary,
        ];
    }

    /**
     * Program-level Overall Articulation Matrix (All courses vs PO1..PO12 & PSO1..PSO3)
     */
    public function getProgramMatrix(Program $program): array
    {
        $courses = Course::where('program_id', $program->id)->orderBy('semester')->orderBy('course_code')->get();
        $pos = ProgramOutcome::where('program_id', $program->id)->orderBy('po_code')->get();
        $psos = ProgramSpecificOutcome::where('program_id', $program->id)->orderBy('pso_code')->get();

        $rows = [];
        $poGlobal = [];
        $psoGlobal = [];

        foreach ($courses as $c) {
            $cMatrix = $this->getCourseMatrix($c);
            $rows[] = [
                'course_id' => $c->id,
                'course_code' => $c->course_code,
                'course_name' => $c->course_name,
                'semester' => $c->semester,
                'po_averages' => $cMatrix['course_average']['po'],
                'pso_averages' => $cMatrix['course_average']['pso'],
            ];

            foreach ($pos as $po) {
                $val = $cMatrix['course_average']['po'][$po->po_code] ?? '-';
                if (is_numeric($val)) {
                    $poGlobal[$po->po_code][] = $val;
                }
            }

            foreach ($psos as $pso) {
                $val = $cMatrix['course_average']['pso'][$pso->pso_code] ?? '-';
                if (is_numeric($val)) {
                    $psoGlobal[$pso->pso_code][] = $val;
                }
            }
        }

        $programAverage = ['po' => [], 'pso' => []];
        foreach ($pos as $po) {
            $vals = $poGlobal[$po->po_code] ?? [];
            $programAverage['po'][$po->po_code] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : 0;
        }
        foreach ($psos as $pso) {
            $vals = $psoGlobal[$pso->pso_code] ?? [];
            $programAverage['pso'][$pso->pso_code] = count($vals) > 0 ? round(array_sum($vals) / count($vals), 2) : 0;
        }

        return [
            'program_id' => $program->id,
            'program_name' => $program->name,
            'pos' => $pos,
            'psos' => $psos,
            'rows' => $rows,
            'program_average' => $programAverage,
        ];
    }
}
