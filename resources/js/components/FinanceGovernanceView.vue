<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
          💰 Criterion 10 • 120 Marks (Finance 30 + Governance 90)
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          Financial Budget Allocation, Expenditure & Governance
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Tracking recurring and non-recurring capital expenditure across CFY, CFYm1, and CFYm2 with utilization auditing.
        </p>
      </div>

      <div class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Overall Utilization</div>
          <div class="text-xl font-black text-emerald-400">{{ sar.live_engines?.budget?.overall_utilization || 94.3 }}%</div>
        </div>
        <div class="h-8 w-px bg-slate-800"></div>
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Finance Claimed Score</div>
          <div class="text-xl font-black text-indigo-400">{{ sar.live_engines?.budget?.claimed_marks || 28 }} / 30.0</div>
        </div>
      </div>
    </div>

    <!-- 3-Year Financial Records Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Financial Expenditure Profile (CFY, CFYm1, CFYm2)
        </h4>
        <span class="text-xs text-slate-400">All figures in INR (₹)</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">Financial Year</th>
              <th class="p-3">Type</th>
              <th class="p-3 text-right">Recurring Budget</th>
              <th class="p-3 text-right">Recurring Spent</th>
              <th class="p-3 text-right">Non-Recurring Budget</th>
              <th class="p-3 text-right">Non-Recurring Spent</th>
              <th class="p-3 text-right font-bold text-white">Total Allocated</th>
              <th class="p-3 text-right font-bold text-emerald-400">Total Spent</th>
              <th class="p-3 text-right font-bold text-indigo-300">Utilization</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="row in sar.live_engines?.budget?.yearly_records" :key="row.financial_year" class="hover:bg-slate-800/30">
              <td class="p-3 font-semibold text-white">{{ row.financial_year }}</td>
              <td class="p-3">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">
                  {{ row.cfy_type }}
                </span>
              </td>
              <td class="p-3 text-right font-mono">₹{{ Number(row.recurring_allocated).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono text-slate-400">₹{{ Number(row.recurring_spent).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono">₹{{ Number(row.non_recurring_allocated).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono text-slate-400">₹{{ Number(row.non_recurring_spent).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono font-bold text-white">₹{{ Number(row.total_allocated).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono font-bold text-emerald-400">₹{{ Number(row.total_spent).toLocaleString() }}</td>
              <td class="p-3 text-right font-mono font-bold text-indigo-400">{{ row.utilization_percentage }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FinanceGovernanceView',
  props: {
    sar: {
      type: Object,
      default: () => ({}),
    },
  },
};
</script>
