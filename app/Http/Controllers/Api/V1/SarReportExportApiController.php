<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Services\SarReportCompilerService;
use App\Domains\Curriculum\Services\ArticulationMatrixService;
use App\Domains\Academic\Models\Program;

class SarReportExportApiController extends Controller
{
    protected SarReportCompilerService $compilerService;
    protected ArticulationMatrixService $matrixService;

    public function __construct(
        SarReportCompilerService $compilerService,
        ArticulationMatrixService $matrixService
    ) {
        $this->compilerService = $compilerService;
        $this->matrixService = $matrixService;
    }

    /**
     * Get Complete Exportable SAR Compilation Document
     */
    public function getFullDocument(int $reportId): JsonResponse
    {
        $report = SarReport::with('program.department.institution', 'academicYear')->findOrFail($reportId);
        $compiled = $this->compilerService->compile($report);
        $programMatrix = $this->matrixService->getProgramMatrix($report->program);

        return response()->json([
            'success' => true,
            'data' => array_merge($compiled, [
                'program_articulation_matrix' => $programMatrix,
            ]),
        ]);
    }
}
