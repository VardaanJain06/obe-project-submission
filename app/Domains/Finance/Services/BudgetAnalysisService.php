<?php

namespace App\Domains\Finance\Services;

use App\Domains\Academic\Models\Program;
use App\Domains\Finance\Models\FinancialBudget;

class BudgetAnalysisService
{
    /**
     * Calculate Criterion 10.1 / 8.1: Financial Budget Allocation vs Expenditure
     * Criterion Marks: Adequacy of Budget (15) + Utilization of Allocated Funds (15) = 30 Marks
     */
    public function analyze(Program $program): array
    {
        $budgets = FinancialBudget::with('items', 'academicYear')
            ->where(function ($q) use ($program) {
                $q->where('program_id', $program->id)
                  ->orWhere('institution_id', $program->institution_id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $yearlyData = [];
        $totalAllocated = 0;
        $totalExpended = 0;

        foreach ($budgets as $b) {
            $recurringAlloc = $b->items->where('category', 'Recurring')->sum('budget_allocated');
            $recurringExp = $b->items->where('category', 'Recurring')->sum('actual_expenditure');
            $nonRecurringAlloc = $b->items->where('category', 'Non-Recurring')->sum('budget_allocated');
            $nonRecurringExp = $b->items->where('category', 'Non-Recurring')->sum('actual_expenditure');

            $alloc = $b->total_budget_allocated > 0 ? $b->total_budget_allocated : ($recurringAlloc + $nonRecurringAlloc);
            $exp = $b->total_actual_expenditure > 0 ? $b->total_actual_expenditure : ($recurringExp + $nonRecurringExp);
            $utilization = $alloc > 0 ? round(($exp / $alloc) * 100, 2) : 0;

            $yearlyData[] = [
                'financial_year' => $b->financial_year_name,
                'cfy_type' => $b->cfy_type,
                'academic_year' => $b->academicYear->name ?? '',
                'recurring_allocated' => $recurringAlloc,
                'recurring_spent' => $recurringExp,
                'non_recurring_allocated' => $nonRecurringAlloc,
                'non_recurring_spent' => $nonRecurringExp,
                'total_allocated' => $alloc,
                'total_spent' => $exp,
                'utilization_percentage' => $utilization,
            ];

            $totalAllocated += $alloc;
            $totalExpended += $exp;
        }

        $overallUtilization = $totalAllocated > 0 ? round(($totalExpended / $totalAllocated) * 100, 2) : 0;
        
        // Claimed marks based on adequacy & utilization (> 85% is optimal)
        $adequacyScore = 13.5; // Max 15
        $utilizationScore = $overallUtilization >= 85 ? 14.5 : round(15 * ($overallUtilization / 100), 1);
        $totalMarks = min(30.0, round($adequacyScore + $utilizationScore, 2));

        return [
            'yearly_records' => $yearlyData,
            'total_allocated' => $totalAllocated,
            'total_spent' => $totalExpended,
            'overall_utilization' => $overallUtilization,
            'adequacy_score' => $adequacyScore,
            'utilization_score' => $utilizationScore,
            'claimed_marks' => $totalMarks,
            'max_marks' => 30.0,
        ];
    }
}
