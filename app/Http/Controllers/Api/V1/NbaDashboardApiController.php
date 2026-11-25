<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Services\SarReportCompilerService;

class NbaDashboardApiController extends Controller
{
    protected SarReportCompilerService $compilerService;

    public function __construct(SarReportCompilerService $compilerService)
    {
        $this->compilerService = $compilerService;
    }

    /**
     * Get overall SAR executive summary and criteria scores
     */
    public function index(Request $request): JsonResponse
    {
        $reportId = $request->query('sar_report_id');

        $report = $reportId 
            ? SarReport::with('program', 'academicYear')->find($reportId)
            : SarReport::with('program', 'academicYear')->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'No SAR report found.'], 404);
        }

        $data = $this->compilerService->compile($report);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
