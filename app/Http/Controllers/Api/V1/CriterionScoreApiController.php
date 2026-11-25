<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Services\CriterionScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CriterionScoreApiController extends Controller
{
    protected CriterionScoreService $criterionScoreService;

    public function __construct(CriterionScoreService $criterionScoreService)
    {
        $this->criterionScoreService = $criterionScoreService;
    }

    /**
     * Get live scores for all 10 NBA criteria.
     */
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

        $scores = $this->criterionScoreService->calculate($report);

        return response()->json([
            'success' => true,
            'data' => $scores,
        ]);
    }
}