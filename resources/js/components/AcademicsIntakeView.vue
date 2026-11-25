<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-blue-500/10 text-blue-400 text-xs font-semibold border border-blue-500/20">
          🏛️ Criterion 4 & Core Academics
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          Academic Programs, Student Intake & Cohorts
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Temporal academic intake tracking (UGT1 to UGT4) and student graduation success windows without overwriting past history.
        </p>
      </div>

      <div class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Sanctioned Intake</div>
          <div class="text-xl font-black text-indigo-400">{{ sar.program?.sanctioned_intake || 120 }} Seats</div>
        </div>
        <div class="h-8 w-px bg-slate-800"></div>
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Tier Classification</div>
          <div class="text-xl font-black text-emerald-400">{{ sar.program?.tier || 'Tier-II' }}</div>
        </div>
      </div>
    </div>

    <!-- Intake Breakdown across CAY, CAYm1, CAYm2 -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Student Intake and Enrolment Profile Across Evaluated Years
        </h4>
        <span class="text-xs text-indigo-400 font-medium">Criterion 4 Baseline</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">Academic Year</th>
              <th class="p-3 text-center">Type</th>
              <th class="p-3 text-right">1st Year (UGT1)</th>
              <th class="p-3 text-right">2nd Year (UGT2)</th>
              <th class="p-3 text-right">3rd Year (UGT3)</th>
              <th class="p-3 text-right">4th Year (UGT4)</th>
              <th class="p-3 text-right font-bold text-white">Total Enrolled</th>
              <th class="p-3 text-right text-emerald-400">Faculty Allocated</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="(row, key) in sar.live_engines?.sfr?.yearly_breakdown" :key="key" class="hover:bg-slate-800/30">
              <td class="p-3 font-semibold text-white">{{ row.academic_year }}</td>
              <td class="p-3 text-center">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">
                  {{ row.type }}
                </span>
              </td>
              <td class="p-3 text-right font-mono">{{ row.ugt1 }}</td>
              <td class="p-3 text-right font-mono">{{ row.ugt2 }}</td>
              <td class="p-3 text-right font-mono">{{ row.ugt3 }}</td>
              <td class="p-3 text-right font-mono">{{ row.ugt4 }}</td>
              <td class="p-3 text-right font-bold text-white font-mono">{{ row.total_students_N }}</td>
              <td class="p-3 text-right font-bold text-emerald-400 font-mono">{{ row.total_faculty_F }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Student Graduation Cohorts (Criterion 4.1 & 4.2) -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Student Graduation Success Rates (Criterion 4.1 & 4.2 - Max 40 Marks)
        </h4>
        <span class="text-xs text-emerald-400 font-semibold">
          Claimed: {{ sar.live_engines?.success_rate?.total_success_marks || 36 }} / 40.0 Marks
        </span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">Batch / Cohort</th>
              <th class="p-3 text-center">Entry - Grad Year</th>
              <th class="p-3 text-right">Admitted (Y1 + Lateral)</th>
              <th class="p-3 text-right">Graduated w/o Backlog</th>
              <th class="p-3 text-right font-bold text-indigo-300">Success Index (w/o Backlog)</th>
              <th class="p-3 text-right">Graduated in Stipulated Time</th>
              <th class="p-3 text-right font-bold text-emerald-300">Success Index (Stipulated)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="cohort in sar.live_engines?.success_rate?.cohorts" :key="cohort.batch_name" class="hover:bg-slate-800/30">
              <td class="p-3 font-semibold text-white">{{ cohort.batch_name }}</td>
              <td class="p-3 text-center text-slate-400">{{ cohort.entry_year }} - {{ cohort.graduation_year }}</td>
              <td class="p-3 text-right font-mono">{{ cohort.total_admitted }}</td>
              <td class="p-3 text-right font-mono text-indigo-300">{{ cohort.graduated_without_backlog }}</td>
              <td class="p-3 text-right font-mono font-bold text-indigo-400">{{ cohort.si_without_backlog }}</td>
              <td class="p-3 text-right font-mono text-emerald-300">{{ cohort.graduated_in_stipulated_time }}</td>
              <td class="p-3 text-right font-mono font-bold text-emerald-400">{{ cohort.si_stipulated_time }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AcademicsIntakeView',
  props: {
    sar: {
      type: Object,
      default: () => ({}),
    },
  },
};
</script>
