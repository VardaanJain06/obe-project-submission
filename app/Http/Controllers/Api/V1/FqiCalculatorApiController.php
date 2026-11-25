<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\Academic\Models\Program;
use App\Domains\Faculty\Services\FqiCalculatorService;
use App\Domains\Faculty\Services\CadreRatioCalculatorService;

class FqiCalculatorApiController extends Controller
{
    protected FqiCalculatorService $fqiService;
    protected CadreRatioCalculatorService $cadreService;

    public function __construct(FqiCalculatorService $fqiService, CadreRatioCalculatorService $cadreService)
    {
        $this->fqiService = $fqiService;
        $this->cadreService = $cadreService;
    }

    /**
     * Get FQI & Cadre metrics
     */
    public function getMetrics(Request $request): JsonResponse
    {
        $programId = $request->query('program_id');
        $program = $programId ? Program::find($programId) : Program::first();

        if (!$program) {
            return response()->json(['success' => false, 'message' => 'Program not found'], 404);
        }

        $fqiData = $this->fqiService->calculate($program);
        $cadreData = $this->cadreService->calculate($program);

        return response()->json([
            'success' => true,
            'data' => [
                'fqi' => $fqiData,
                'cadre_ratio' => $cadreData,
            ],
        ]);
    }

    /**
     * Live Simulator for FQI: FQI = 2.5 * [(10X + 4Y) / RF]
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phd_faculty_x' => 'required|numeric|min:0',
            'mtech_faculty_y' => 'required|numeric|min:0',
            'total_students' => 'required|numeric|min:1',
        ]);

        $x = $validated['phd_faculty_x'];
        $y = $validated['mtech_faculty_y'];
        $students = $validated['total_students'];
        $rf = max(1, ceil($students / 20));

        $rawFqi = round(2.5 * ((10 * $x + 4 * $y) / $rf), 2);
        $claimedScore = min(25.0, $rawFqi);

        return response()->json([
            'success' => true,
            'data' => [
                'phd_faculty_x' => $x,
                'mtech_faculty_y' => $y,
                'total_students' => $students,
                'required_faculty_rf' => $rf,
                'raw_fqi' => $rawFqi,
                'claimed_score' => $claimedScore,
                'max_score' => 25.0,
                'percentage' => round(($claimedScore / 25.0) * 100, 1),
            ],
        ]);
    }
}
