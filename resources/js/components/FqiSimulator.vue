<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-purple-500/10 text-purple-400 text-xs font-semibold border border-purple-500/20">
          Criterion 5.2 & 5.3 • 50 Marks (Cadre 25 + FQI 25)
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          Faculty Qualification Index (FQI) & Cadre Proportion
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Formula: <code class="text-purple-300 font-mono">FQI = 2.5 × [(10X + 4Y) / RF]</code> where <code class="text-purple-300 font-mono">X = Ph.D. Faculty, Y = M.Tech Faculty, RF = Required Faculty (N / 20)</code>
        </p>
      </div>

      <!-- Quick Summary Score -->
      <div class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Average FQI Score</div>
          <div class="text-xl font-black text-emerald-400">
            {{ fqi?.claimed_marks || 0 }} <span class="text-xs text-slate-400 font-normal">/ 25.0</span>
          </div>
        </div>
        <div class="h-8 w-px bg-slate-800"></div>
        <div>
          <div class="text-[10px] text-slate-400 uppercase font-bold">Cadre Proportion</div>
          <div class="text-xl font-black text-indigo-400">
            {{ cadre?.claimed_marks || 0 }} <span class="text-xs text-slate-400 font-normal">/ 25.0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- FQI Breakdown Table -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- FQI Table -->
      <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
        <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
          <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
            Faculty Qualifications Breakdown (3 Years)
          </h4>
          <span class="text-xs text-purple-400 font-medium">Criterion 5.3</span>
        </div>
        <div class="p-4 space-y-3">
          <div v-for="(row, key) in fqi?.yearly_breakdown" :key="key"
               class="p-3 rounded-lg bg-slate-950/70 border border-slate-800 flex items-center justify-between">
            <div>
              <div class="font-bold text-sm text-white">{{ row.academic_year }} ({{ row.type }})</div>
              <div class="text-xs text-slate-400 mt-0.5">
                Ph.D. (X): <strong class="text-emerald-400">{{ row.phd_count_X }}</strong> •
                M.Tech (Y): <strong class="text-indigo-400">{{ row.mtech_count_Y }}</strong> •
                RF: <strong class="text-slate-300">{{ row.required_faculty_RF }}</strong>
              </div>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-purple-400">{{ row.fqi_capped }} / 25.0</div>
              <div class="text-[10px] text-slate-400">Raw: {{ row.fqi_raw }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cadre Ratio Breakdown -->
      <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
        <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
          <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
            Cadre Proportion Distribution (Target 1 : 2 : 6)
          </h4>
          <span class="text-xs text-indigo-400 font-medium">Criterion 5.2</span>
        </div>
        <div class="p-4 space-y-3">
          <div v-for="(row, key) in cadre?.yearly_breakdown" :key="key"
               class="p-3 rounded-lg bg-slate-950/70 border border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-bold text-sm text-white">{{ row.academic_year }}</span>
              <span class="text-sm font-bold text-emerald-400">{{ row.score }} / 25.0 Pts</span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs">
              <div class="p-2 rounded bg-slate-900 border border-slate-800">
                <div class="text-[10px] text-slate-400">Professors (AF1/RF1)</div>
                <div class="font-bold text-white">{{ row.actual_professors }} / {{ row.required_professors }}</div>
              </div>
              <div class="p-2 rounded bg-slate-900 border border-slate-800">
                <div class="text-[10px] text-slate-400">Assoc Profs (AF2/RF2)</div>
                <div class="font-bold text-white">{{ row.actual_assoc_profs }} / {{ row.required_assoc_profs }}</div>
              </div>
              <div class="p-2 rounded bg-slate-900 border border-slate-800">
                <div class="text-[10px] text-slate-400">Asst Profs (AF3/RF3)</div>
                <div class="font-bold text-white">{{ row.actual_asst_profs }} / {{ row.required_asst_profs }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Live FQI Simulator -->
    <div class="glass-card rounded-xl p-5 border border-purple-500/30 bg-slate-900/50">
      <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
        <div>
          <h4 class="text-sm font-bold text-white flex items-center gap-2">
            <span>⚡</span> Interactive FQI Formula Simulator
          </h4>
          <p class="text-xs text-slate-400">Simulate hiring PhD vs M.Tech faculty against student intake</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-[11px] font-semibold text-emerald-400">Number of Ph.D. Faculty (X)</label>
          <input type="number" v-model.number="sim.x" @input="runSim"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-purple-500 outline-none" />
        </div>
        <div>
          <label class="text-[11px] font-semibold text-indigo-400">Number of M.Tech Faculty (Y)</label>
          <input type="number" v-model.number="sim.y" @input="runSim"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-purple-500 outline-none" />
        </div>
        <div>
          <label class="text-[11px] font-semibold text-slate-300">Total Enrolled Students (N)</label>
          <input type="number" v-model.number="sim.students" @input="runSim"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-white focus:border-purple-500 outline-none" />
        </div>
      </div>

      <div class="mt-4 p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-6">
          <div>
            <div class="text-[10px] text-slate-400 uppercase font-bold">Required Faculty (RF = N/20)</div>
            <div class="text-lg font-bold text-white">{{ simResult.required_faculty_rf }}</div>
          </div>
          <div>
            <div class="text-[10px] text-slate-400 uppercase font-bold">Calculated FQI</div>
            <div class="text-lg font-bold text-purple-400 font-mono">{{ simResult.raw_fqi }}</div>
          </div>
          <div>
            <div class="text-[10px] text-slate-400 uppercase font-bold">Claimed Score (Max 25)</div>
            <div class="text-xl font-black text-emerald-400">{{ simResult.claimed_score }} / 25.0</div>
          </div>
        </div>
        <div>
          <div class="text-xs text-slate-400 text-right">Qualification Score Rate</div>
          <div class="text-base font-extrabold text-emerald-400">{{ simResult.percentage }}%</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FqiSimulator',
  props: {
    fqi: {
      type: Object,
      default: () => ({}),
    },
    cadre: {
      type: Object,
      default: () => ({}),
    },
    program: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      sim: {
        x: 7,
        y: 15,
        students: 495,
      },
      simResult: {
        required_faculty_rf: 25,
        raw_fqi: 13.0,
        claimed_score: 13.0,
        percentage: 52.0,
      },
    };
  },
  mounted() {
    this.runSim();
  },
  methods: {
    async runSim() {
      try {
        const res = await axios.post('/api/v1/nba/fqi-simulate', {
          phd_faculty_x: this.sim.x || 0,
          mtech_faculty_y: this.sim.y || 0,
          total_students: Math.max(1, this.sim.students || 1),
        });
        if (res.data && res.data.success) {
          this.simResult = res.data.data;
        }
      } catch (err) {
        console.error('FQI sim error:', err);
      }
    },
  },
};
</script>
