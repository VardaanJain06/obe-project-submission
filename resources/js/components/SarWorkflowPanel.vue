<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-amber-500/10 text-amber-400 text-xs font-semibold border border-amber-500/20">
          Compliance State Engine & Audit Trail
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          SAR Approval Progression & Workflow Verification
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Guarantees document immutability during evaluation and maintains a chronological verification history.
        </p>
      </div>

      <div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border"
              :class="workflow.is_locked ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-amber-500/20 text-amber-300 border-amber-500/40'">
          <span class="w-2 h-2 rounded-full mr-2" :class="workflow.is_locked ? 'bg-emerald-400' : 'bg-amber-400 animate-ping'"></span>
          {{ workflow.is_locked ? '🔒 DOCUMENT LOCKED & FROZEN' : '✏️ EDITING ACTIVE' }}
        </span>
      </div>
    </div>

    <!-- Visual Progression Stepper -->
    <div class="glass-card rounded-xl p-6 border border-slate-800">
      <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-6">
        Accreditation Workflow Pipeline
      </h4>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 relative">
        <div v-for="(state, idx) in workflowSteps" :key="state.id"
             class="p-4 rounded-xl border relative transition"
             :class="stepClass(state.id)">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold font-mono">0{{ idx + 1 }}</span>
            <span class="text-sm">{{ state.icon }}</span>
          </div>
          <div class="font-bold text-sm mt-2 text-white">{{ state.title }}</div>
          <div class="text-[11px] text-slate-400 mt-1">{{ state.role }}</div>

          <div v-if="workflow.current_state === state.id" class="mt-3">
            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500 text-white animate-pulse">
              Current Active Stage
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- State Transition Action Control -->
    <div v-if="!workflow.is_locked" class="glass-card rounded-xl p-5 border border-indigo-500/30 bg-slate-900/60 space-y-4">
      <div class="flex items-center justify-between border-b border-slate-800 pb-3">
        <h4 class="text-sm font-bold text-white flex items-center gap-2">
          <span>🛡️</span> Advance SAR Workflow State
        </h4>
        <span class="text-xs text-slate-400">Authorized Officer Actions</span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-[11px] font-semibold text-slate-300">Action Officer Name</label>
          <input type="text" v-model="form.userName"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none" />
        </div>
        <div>
          <label class="text-[11px] font-semibold text-slate-300">Officer Role</label>
          <select v-model="form.userRole"
                  class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
            <option value="HOD">HOD (Department Head)</option>
            <option value="IQAC Admin">IQAC Admin</option>
            <option value="Principal">Principal / Director</option>
            <option value="Faculty">NBA Coordinator</option>
          </select>
        </div>
        <div>
          <label class="text-[11px] font-semibold text-indigo-400">Target State Transition</label>
          <select v-model="form.targetState"
                  class="w-full mt-1 bg-slate-950 border border-indigo-500/50 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-400 outline-none">
            <option v-for="(label, key) in workflow.allowed_transitions" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
        </div>
      </div>

      <div>
        <label class="text-[11px] font-semibold text-slate-300">Verification & Recommendation Remarks</label>
        <textarea v-model="form.remarks" rows="2" placeholder="e.g. All matrices and SFR values verified against department logbooks."
                  class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none"></textarea>
      </div>

      <div class="flex justify-end">
        <button @click="openApprovalModal" :disabled="submitting"
                class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-indigo-600/30 transition disabled:opacity-50">
          <span>🚀</span> Execute Transition & Log Audit Trail
        </button>
      </div>
    </div>

    <!-- Chronological Audit Trail Timeline -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Compliance & Verification Audit Trail (Immutable Event Log)
        </h4>
        <span class="text-xs text-slate-400">{{ workflow.audit_logs?.length || 0 }} Log Entries</span>
      </div>

      <div class="p-4 divide-y divide-slate-800/60">
        <div v-for="log in workflow.audit_logs" :key="log.id" class="py-3 flex items-start space-x-3 text-xs">
          <div class="w-7 h-7 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-xs font-bold text-indigo-400 shrink-0">
            ✓
          </div>
          <div class="flex-1">
            <div class="flex items-center justify-between">
              <span class="font-bold text-white">{{ log.user_name }} ({{ log.user_role }})</span>
              <span class="text-[11px] text-slate-500 font-mono">{{ formatDate(log.created_at) }}</span>
            </div>
            <p class="text-slate-300 mt-1 leading-relaxed">{{ log.description }}</p>
            <div class="mt-1 flex items-center gap-2 text-[10px] text-slate-500 font-mono">
              <span>Module: {{ log.domain_module || 'SAR' }}</span>
              <span>•</span>
              <span>Action: {{ log.action }}</span>
              <span>•</span>
              <span>IP: {{ log.ip_address }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Approval Confirmation Modal -->
<div
  v-if="showApprovalModal"
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
>
  <div class="w-full max-w-lg rounded-xl border border-slate-700 bg-slate-900 p-6 shadow-2xl">
    <div class="mb-5 flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold text-white">
          Confirm SAR State Transition
        </h3>
        <p class="mt-1 text-sm text-slate-400">
          Please review the approval details before continuing.
        </p>
      </div>

      <button
        type="button"
        class="text-xl text-slate-400 hover:text-white"
        @click="showApprovalModal = false"
      >
        ✕
      </button>
    </div>

    <div class="space-y-3 rounded-lg border border-slate-700 bg-slate-950 p-4">
      <div class="flex justify-between gap-4">
        <span class="text-sm text-slate-400">Current State</span>
        <span class="text-sm font-semibold text-white">
          {{ workflow.current_state }}
        </span>
      </div>

      <div class="flex justify-between gap-4">
        <span class="text-sm text-slate-400">Target State</span>
        <span class="text-sm font-semibold text-indigo-300">
          {{ form.targetState }}
        </span>
      </div>

      <div class="flex justify-between gap-4">
        <span class="text-sm text-slate-400">Officer</span>
        <span class="text-sm font-semibold text-white">
          {{ form.userName }}
        </span>
      </div>

      <div class="flex justify-between gap-4">
        <span class="text-sm text-slate-400">Role</span>
        <span class="text-sm font-semibold text-white">
          {{ form.userRole }}
        </span>
      </div>

      <div>
        <p class="mb-1 text-sm text-slate-400">Remarks</p>
        <p class="text-sm text-white">
          {{ form.remarks || 'No remarks provided.' }}
        </p>
      </div>
    </div>

    <div class="mt-5 flex justify-end gap-3">
      <button
        type="button"
        class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:bg-slate-800"
        @click="showApprovalModal = false"
      >
        Cancel
      </button>

      <button
        type="button"
        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
        :disabled="submitting"
        @click="executeTransition"
      >
        {{ submitting ? 'Processing...' : 'Confirm & Approve' }}
      </button>
    </div>
  </div>
</div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'SarWorkflowPanel',
  props: {
    reportId: {
      type: Number,
      default: 1,
    },
  },
  data() {
    return {
      workflow: {},
      submitting: false,
      showApprovalModal: false,
      form: {
        userName: 'Dr. Arvind Mehra',
        userRole: 'HOD',
        targetState: '',
        remarks: 'All matrices verified against course files.',
      },
      workflowSteps: [
        { id: 'DRAFT', title: '1. Drafting Phase', role: 'Faculty Mark Entry', icon: '📝' },
        { id: 'DEPARTMENT_CONSOLIDATION', title: '2. Dept Consolidation', role: 'HOD Verification', icon: '🏛️' },
        { id: 'INSTITUTIONAL_REVIEW', title: '3. Institutional Review', role: 'IQAC / Principal Audit', icon: '🔍' },
        { id: 'APPROVED_LOCKED', title: '4. Approved & Frozen', role: 'Final Immutable SAR', icon: '🔒' },
      ],
    };
  },
  mounted() {
    this.fetchWorkflow();
  },
  methods: {
    async fetchWorkflow() {
      try {
        const res = await axios.get(`/api/v1/nba/workflow/${this.reportId}`);
        if (res.data && res.data.success) {
          this.workflow = res.data.data;
          const available = Object.keys(this.workflow.allowed_transitions || {});
          if (available.length > 0) {
            this.form.targetState = available[0];
          }
        }
      } catch (err) {
        console.error('Failed to load workflow:', err);
      }
    },
    openApprovalModal() {
      if (!this.form.targetState) return;
      this.showApprovalModal = true;
    },
    async executeTransition() {
      if (!this.form.targetState) return;
      this.submitting = true;
      try {
        const res = await axios.post(`/api/v1/nba/workflow/${this.reportId}/transition`, {
          target_state: this.form.targetState,
          user_name: this.form.userName,
          user_role: this.form.userRole,
          remarks: this.form.remarks,
        });
        if (res.data && res.data.success) {
  this.showApprovalModal = false;
  this.fetchWorkflow();
  this.$emit('state-changed');
}
      } catch (err) {
        alert(err.response?.data?.message || 'Transition failed');
      } finally {
        this.submitting = false;
      }
    },
    stepClass(stepId) {
      if (this.workflow.current_state === stepId) {
        return 'bg-indigo-600/20 border-indigo-500 shadow-lg shadow-indigo-500/10';
      }
      return 'bg-slate-950/40 border-slate-800 text-slate-400';
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },
  },
};
</script>
