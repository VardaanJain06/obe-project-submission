<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains\SAR\Models\SarReport;
use App\Domains\SAR\Models\SarAuditLog;
use App\Domains\SAR\StateMachine\SarStateMachine;
use Exception;

class SarWorkflowApiController extends Controller
{
    protected SarStateMachine $stateMachine;

    public function __construct(SarStateMachine $stateMachine)
    {
        $this->stateMachine = $stateMachine;
    }

    /**
     * Get SAR status, available transitions, and audit logs
     */
    public function getStatus(int $reportId): JsonResponse
    {
        $report = SarReport::with('program', 'academicYear', 'auditLogs')->find($reportId);
        if (!$report) {
            return response()->json(['success' => false, 'message' => 'SAR Report not found'], 404);
        }

        $allStates = SarStateMachine::getAvailableStates();
        $currentState = $report->current_state;

        // Determine allowed next transitions
        $allowedTransitions = [];
        foreach (array_keys($allStates) as $state) {
            if (SarStateMachine::canTransition($currentState, $state)) {
                $allowedTransitions[$state] = $allStates[$state];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'report_id' => $report->id,
                'title' => $report->title,
                'current_state' => $currentState,
                'current_state_label' => $allStates[$currentState] ?? $currentState,
                'is_locked' => $report->is_locked,
                'submitted_by' => $report->submitted_by,
                'submitted_at' => $report->submitted_at ? $report->submitted_at->format('d-M-Y H:i') : null,
                'hod_verified_by' => $report->hod_verified_by,
                'iqac_approved_by' => $report->iqac_approved_by,
                'locked_at' => $report->locked_at ? $report->locked_at->format('d-M-Y H:i') : null,
                'allowed_transitions' => $allowedTransitions,
                'all_states' => $allStates,
                'audit_logs' => $report->auditLogs->take(30),
            ],
        ]);
    }

    /**
     * Trigger a state transition
     */
    public function transition(Request $request, int $reportId): JsonResponse
    {
        $validated = $request->validate([
            'target_state' => 'required|string',
            'user_name' => 'required|string|max:100',
            'user_role' => 'required|string|in:Faculty,HOD,IQAC Admin,Principal,Evaluator',
            'remarks' => 'nullable|string|max:500',
        ]);

        $report = SarReport::findOrFail($reportId);

        try {
            $updatedReport = $this->stateMachine->transition(
                $report,
                $validated['target_state'],
                $validated['user_name'],
                $validated['user_role'],
                $validated['remarks'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully transitioned SAR to {$validated['target_state']}.",
                'current_state' => $updatedReport->current_state,
                'is_locked' => $updatedReport->is_locked,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
