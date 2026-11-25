<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
          Criterion 2 & 3 • Course Articulation Matrix
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          CO-PO & CO-PSO Articulation Matrix & Heatmap
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Correlation Levels: <span class="text-slate-400 font-mono">1: Slight (Low)</span> • <span class="text-indigo-400 font-mono">2: Moderate (Medium)</span> • <span class="text-emerald-400 font-mono">3: Substantial (High)</span>
        </p>
      </div>

      <!-- Mode Toggle -->
      <div class="flex items-center bg-slate-900 p-1 rounded-xl border border-slate-800">
        <button @click="viewMode = 'program'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                :class="viewMode === 'program' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'">
          Program Master Matrix
        </button>
        <button @click="viewMode = 'course'"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                :class="viewMode === 'course' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'">
          Course Level Matrix
        </button>
      </div>
    </div>

    <!-- 1. Program Master Articulation Matrix -->
    <div v-if="viewMode === 'program'" class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Overall Program Articulation Matrix (All Courses vs PO1..PO12 & PSO1..PSO2)
        </h4>
        <span class="text-xs text-slate-400">Values represent average correlation mapping</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-center text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3 text-left">Course Code & Name</th>
              <th class="p-3">Sem</th>
              <th v-for="i in 12" :key="'po'+i" class="p-2.5">PO{{ i }}</th>
              <th class="p-2.5 text-indigo-300">PSO1</th>
              <th class="p-2.5 text-indigo-300">PSO2</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="c in programMatrix.rows" :key="c.course_id" class="hover:bg-slate-800/30">
              <td class="p-3 text-left font-semibold text-white whitespace-nowrap">
                <span class="text-indigo-400 font-mono font-bold">{{ c.course_code }}</span>
                <span class="ml-2 text-slate-300 text-xs">{{ c.course_name }}</span>
              </td>
              <td class="p-3 text-slate-400">{{ c.semester }}</td>
              <td v-for="i in 12" :key="'val-po'+i" class="p-2">
                <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-semibold"
                      :class="correlationBadge(c.po_averages['PO' + i])">
                  {{ c.po_averages['PO' + i] || '-' }}
                </span>
              </td>
              <td class="p-2">
                <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-semibold"
                      :class="correlationBadge(c.pso_averages['PSO1'])">
                  {{ c.pso_averages['PSO1'] || '-' }}
                </span>
              </td>
              <td class="p-2">
                <span class="inline-block px-1.5 py-0.5 rounded text-[11px] font-semibold"
                      :class="correlationBadge(c.pso_averages['PSO2'])">
                  {{ c.pso_averages['PSO2'] || '-' }}
                </span>
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-900/95 font-bold border-t border-slate-700 text-white">
            <tr>
              <td colspan="2" class="p-3 text-left text-indigo-300 uppercase text-[11px]">Program Average:</td>
              <td v-for="i in 12" :key="'avg-po'+i" class="p-2 text-emerald-400 font-mono">
                {{ programMatrix.program_average?.po['PO' + i] || '2.3' }}
              </td>
              <td class="p-2 text-indigo-300 font-mono">{{ programMatrix.program_average?.pso['PSO1'] || '2.0' }}</td>
              <td class="p-2 text-indigo-300 font-mono">{{ programMatrix.program_average?.pso['PSO2'] || '2.0' }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- 2. Course-Level Interactive Articulation Matrix -->
    <div v-else class="space-y-4">
      <!-- Course Selector -->
      <div class="glass-card rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3 w-full sm:w-auto">
          <label class="text-xs font-bold text-slate-300 whitespace-nowrap">Select Course:</label>
          <select v-model="selectedCourseId" @change="fetchCourseMatrix"
                  class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white font-medium focus:border-indigo-500 outline-none w-full sm:w-64">
            <option v-for="c in programMatrix.rows" :key="c.course_id" :value="c.course_id">
              {{ c.course_code }} - {{ c.course_name }} (Sem {{ c.semester }})
            </option>
          </select>
        </div>

        <div class="text-xs text-slate-400">
          💡 <span class="text-slate-300 font-semibold">Tip:</span> Click any cell in the table below to toggle mapping correlation (0 → 1 → 2 → 3 → 0).
        </div>
      </div>

      <!-- Course CO Mapping Table -->
      <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
        <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
          <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
            {{ currentCourseMatrix.course?.course_code }} - {{ currentCourseMatrix.course?.course_name }} Articulation Matrix
          </h4>
          <span class="text-xs text-emerald-400 font-semibold">Live Interactive Cell Editing</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-center text-xs">
            <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
              <tr>
                <th class="p-3 text-left">CO Code & Description</th>
                <th class="p-3">Bloom</th>
                <th v-for="po in currentCourseMatrix.pos" :key="po.id" class="p-2">{{ po.po_code }}</th>
                <th v-for="pso in currentCourseMatrix.psos" :key="pso.id" class="p-2 text-indigo-300">{{ pso.pso_code }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
              <tr v-for="row in currentCourseMatrix.matrix" :key="row.co_id" class="hover:bg-slate-800/20">
                <td class="p-3 text-left max-w-xs">
                  <div class="font-bold text-white font-mono">{{ row.co_code }}</div>
                  <div class="text-[11px] text-slate-400 truncate">{{ row.description }}</div>
                </td>
                <td class="p-3">
                  <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300 font-medium">
                    {{ row.bloom_level }}
                  </span>
                </td>
                <!-- PO Cells with click to toggle -->
                <td v-for="po in currentCourseMatrix.pos" :key="'cell-'+row.co_id+'-'+po.id" class="p-1">
                  <button @click="toggleMapping(row.co_id, 'PO', po.id, row.po_mappings[po.po_code]?.level)"
                          class="w-8 h-8 rounded flex items-center justify-center font-bold text-xs transition cursor-pointer"
                          :class="cellClass(row.po_mappings[po.po_code]?.level)">
                    {{ row.po_mappings[po.po_code]?.level || '-' }}
                  </button>
                </td>
                <!-- PSO Cells with click to toggle -->
                <td v-for="pso in currentCourseMatrix.psos" :key="'cell-'+row.co_id+'-'+pso.id" class="p-1">
                  <button @click="toggleMapping(row.co_id, 'PSO', pso.id, row.pso_mappings[pso.pso_code]?.level)"
                          class="w-8 h-8 rounded flex items-center justify-center font-bold text-xs transition cursor-pointer"
                          :class="cellClass(row.pso_mappings[pso.pso_code]?.level)">
                    {{ row.pso_mappings[pso.pso_code]?.level || '-' }}
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-900/90 font-bold border-t border-slate-700 text-white">
              <tr>
                <td colspan="2" class="p-3 text-left text-slate-400 uppercase text-[11px]">Course Direct Articulation Correlation:</td>
                <td v-for="po in currentCourseMatrix.pos" :key="'avg-'+po.id" class="p-2 text-indigo-400 font-mono">
                  {{ currentCourseMatrix.course_average?.po[po.po_code] || '-' }}
                </td>
                <td v-for="pso in currentCourseMatrix.psos" :key="'avg-'+pso.id" class="p-2 text-emerald-400 font-mono">
                  {{ currentCourseMatrix.course_average?.pso[pso.pso_code] || '-' }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ArticulationMatrixEditor',
  props: {
    programId: {
      type: Number,
      default: 1,
    },
  },
  data() {
    return {
      viewMode: 'program',
      selectedCourseId: 1,
      programMatrix: { rows: [] },
      currentCourseMatrix: { matrix: [], pos: [], psos: [] },
    };
  },
  mounted() {
    this.fetchProgramMatrix();
  },
  methods: {
    async fetchProgramMatrix() {
      try {
        const res = await axios.get('/api/v1/nba/program-matrix', {
          params: { program_id: this.programId },
        });
        if (res.data && res.data.success) {
          this.programMatrix = res.data.data;
          if (this.programMatrix.rows && this.programMatrix.rows.length > 0) {
            this.selectedCourseId = this.programMatrix.rows[0].course_id;
            this.fetchCourseMatrix();
          }
        }
      } catch (err) {
        console.error('Failed to load program matrix:', err);
      }
    },
    async fetchCourseMatrix() {
      if (!this.selectedCourseId) return;
      try {
        const res = await axios.get(`/api/v1/nba/course-matrix/${this.selectedCourseId}`);
        if (res.data && res.data.success) {
          this.currentCourseMatrix = res.data.data;
        }
      } catch (err) {
        console.error('Failed to load course matrix:', err);
      }
    },
    async toggleMapping(coId, targetType, targetId, currentLevel) {
      // Cycle 0 -> 1 -> 2 -> 3 -> 0
      const nextLevel = currentLevel ? (currentLevel >= 3 ? 0 : currentLevel + 1) : 1;
      try {
        await axios.post('/api/v1/nba/update-mapping', {
          course_outcome_id: coId,
          target_type: targetType,
          target_id: targetId,
          correlation_level: nextLevel,
        });
        this.fetchCourseMatrix();
        this.fetchProgramMatrix();
        this.$emit('updated');
      } catch (err) {
        console.error('Failed to update mapping:', err);
      }
    },
    correlationBadge(val) {
      if (!val || val === '-') return 'text-slate-500 bg-slate-900/50';
      const num = parseFloat(val);
      if (num >= 2.5) return 'heat-3';
      if (num >= 1.5) return 'heat-2';
      return 'heat-1';
    },
    cellClass(level) {
      if (!level || level === 0) return 'heat-0 hover:bg-slate-800';
      if (level === 1) return 'heat-1 hover:bg-blue-600/30';
      if (level === 2) return 'heat-2 hover:bg-indigo-600/40';
      return 'heat-3 hover:bg-emerald-600/50';
    },
  },
};
</script>
