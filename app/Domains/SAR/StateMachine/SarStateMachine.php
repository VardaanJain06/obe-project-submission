<?php

namespace App\Domains\SAR\StateMachine;

use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Models\SarAuditLog;
use Exception;

class SarStateMachine
{
    public const STATE_DRAFT = 'DRAFT';
    public const STATE_DEPARTMENT = 'DEPARTMENT_CONSOLIDATION';
    public const STATE_INSTITUTIONAL = 'INSTITUTIONAL_REVIEW';
    public const STATE_LOCKED = 'APPROVED_LOCKED';
    public const STATE_ARCHIVED = 'ARCHIVED';

    public static function getAvailableStates(): array
    {
        return [
            self::STATE_DRAFT => 'Drafting Phase (Faculty Entry)',
            self::STATE_DEPARTMENT => 'Department Consolidation (HOD Review)',
            self::STATE_INSTITUTIONAL => 'Institutional Review (IQAC / Principal)',
            self::STATE_LOCKED => 'Approved & Locked (Final SAR Freezed)',
            self::STATE_ARCHIVED => 'Archived (Historical Cycle)',
        ];
    }

    /**
     * Check if a transition is valid
     */
    public static function canTransition(string $from, string $to): bool
    {
        $validTransitions = [
            self::STATE_DRAFT => [self::STATE_DEPARTMENT],
            self::STATE_DEPARTMENT => [self::STATE_DRAFT, self::STATE_INSTITUTIONAL],
            self::STATE_INSTITUTIONAL => [self::STATE_DEPARTMENT, self::STATE_LOCKED],
            self::STATE_LOCKED => [self::STATE_INSTITUTIONAL, self::STATE_ARCHIVED],
            self::STATE_ARCHIVED => [],
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Transition report to a new state with audit logging
     */
    public function transition(
        SarReport $report, 
        string $targetState, 
        string $userName = 'HOD Computer Science', 
        string $userRole = 'HOD', 
        ?string $remarks = null
    ): SarReport {
        $currentState = $report->current_state;

        if (!self::canTransition($currentState, $targetState)) {
            throw new Exception("Invalid state transition from '{$currentState}' to '{$targetState}'.");
        }

        $report->current_state = $targetState;

        if ($targetState === self::STATE_DEPARTMENT) {
            $report->submitted_by = $userName;
            $report->submitted_at = now();
        } elseif ($targetState === self::STATE_INSTITUTIONAL) {
            $report->hod_verified_by = $userName;
        } elseif ($targetState === self::STATE_LOCKED) {
            $report->iqac_approved_by = $userName;
            $report->is_locked = true;
            $report->locked_at = now();
        }

        if ($remarks) {
            $report->workflow_notes = ($report->workflow_notes ? $report->workflow_notes . "\n" : '') . "[" . now()->format('d-M-Y H:i') . "] {$userName} ({$userRole}): {$remarks}";
        }

        $report->save();

        // Audit Log Entry
        SarAuditLog::create([
            'sar_report_id' => $report->id,
            'user_name' => $userName,
            'user_role' => $userRole,
            'action' => 'STATE_TRANSITION',
            'domain_module' => 'SAR',
            'payload' => [
                'from_state' => $currentState,
                'to_state' => $targetState,
                'remarks' => $remarks,
            ],
            'description' => "Transitioned SAR status from '{$currentState}' to '{$targetState}' by {$userName} ({$userRole}).",
            'ip_address' => request()->ip() ?? '127.0.0.1',
        ]);

        return $report;
    }
}
