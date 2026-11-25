<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Services\NbaReadinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NbaReadinessApiController extends Controller
{
    protected NbaReadinessService $nbaReadinessService;

    public function __construct(NbaReadinessService $nbaReadinessService)
    {
        $this->nbaReadinessService = $nbaReadinessService;
    }

    public function index(Request $request): JsonResponse
    {
        $reportId = $request->query('sar_report_id');

        $report = $reportId
            ? SarReport::with('program', 'academicYear')->find($reportId)
            : SarReport::with('program', 'academicYear')->first();

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'No SAR report found.',
            ], 404);
        }

        $readiness = $this->nbaReadinessService->calculateReadiness($report);

        return response()->json([
            'success' => true,
            'data' => $readiness,
        ]);
    }
}
