<?php

namespace App\Domains\SAR\Services;

use App\Domains\SAR\Models\SarReport;

class NbaReadinessService
{
    protected CriterionScoreService $criterionScoreService;

    public function __construct(CriterionScoreService $criterionScoreService)
    {
        $this->criterionScoreService = $criterionScoreService;
    }

    public function calculateReadiness(SarReport $report): array
    {
        $scores = $this->criterionScoreService->calculate($report);
        $totalScore = $scores['total_claimed_marks'] ?? 0;

        if ($totalScore >= 900) {
            $tier = 'Platinum';
        } elseif ($totalScore >= 750) {
            $tier = 'Gold';
        } elseif ($totalScore >= 600) {
            $tier = 'Silver';
        } else {
            $tier = 'At Risk';
        }

        return [
            'report_id' => $report->id,
            'total_score' => $totalScore,
            'tier' => $tier,
            'status' => $tier,
        ];
    }
}
