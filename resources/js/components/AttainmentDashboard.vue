<template>
  <div class="space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
          <AppIcon name="target" size="12" />
          <span>Criterion 3.2 & 3.3 • 120 Marks</span>
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          PO & PSO Attainment Evaluation & Marks Entry
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Formula: <code class="text-emerald-300 font-mono">Final PO Attainment = (0.80 × Direct) + (0.20 × Indirect)</code>
        </p>
      </div>

      <!-- Quick Action Buttons -->
      <div class="flex items-center space-x-3">
        <button @click="showMarksModal = true"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          Record Assessment Marks
        </button>

        <div class="flex items-center space-x-3 bg-slate-900 border border-slate-800 px-3.5 py-1.5 rounded-xl">
          <div>
            <div class="text-[10px] text-slate-400 uppercase font-bold">Overall PO Score</div>
            <div class="text-lg font-black text-emerald-400">
              {{ attainment?.overall_po_average || 2.62 }} <span class="text-xs text-slate-400 font-normal">/ 3.0</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div v-if="successMsg" class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between">
      <span>✅ {{ successMsg }}</span>
      <button @click="successMsg = ''" class="text-emerald-400 hover:text-white">✕</button>
    </div>

    <!-- PO Attainment Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Program Outcome (PO1 to PO12) Attainment Matrix (Direct 80% + Indirect 20%)
        </h4>
        <span class="text-xs text-slate-400">Benchmark Target: 2.50 / 3.0 (83.3%)</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">PO Code</th>
              <th class="p-3">Program Outcome Description</th>
              <th class="p-3 text-right">Direct (80%)</th>
              <th class="p-3 text-right">Indirect (20%)</th>
              <th class="p-3 text-right font-bold text-white">Final Attainment (3.0)</th>
              <th class="p-3 text-right">Percentage</th>
              <th class="p-3 text-center">Target Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="po in attainment?.po_attainments" :key="po.po_code" class="hover:bg-slate-800/30">
              <td class="p-3 font-bold font-mono text-indigo-400 whitespace-nowrap">{{ po.po_code }}</td>
              <td class="p-3 text-slate-300 max-w-sm">{{ po.title }}</td>
              <td class="p-3 text-right font-mono">{{ po.direct_attainment }}</td>
              <td class="p-3 text-right font-mono text-slate-400">{{ po.indirect_attainment }}</td>
              <td class="p-3 text-right font-mono font-bold text-base text-emerald-400">{{ po.final_attainment }}</td>
              <td class="p-3 text-right font-semibold">{{ po.percentage }}%</td>
              <td class="p-3 text-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="po.is_attained ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'">
                  {{ po.is_attained ? 'Target Met' : 'Action Required' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- PSO Attainment Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="px-4 py-3 bg-slate-900/80 border-b border-slate-800 flex items-center justify-between">
        <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
          Program Specific Outcomes (PSO1 to PSO2) Attainment
        </h4>
        <span class="text-xs text-indigo-400 font-semibold">Specialization Level</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3">PSO Code</th>
              <th class="p-3">Program Specific Outcome Title</th>
              <th class="p-3 text-right">Direct (80%)</th>
              <th class="p-3 text-right">Indirect (20%)</th>
              <th class="p-3 text-right font-bold text-white">Final Score (3.0)</th>
              <th class="p-3 text-right">Percentage</th>
              <th class="p-3 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="pso in attainment?.pso_attainments" :key="pso.pso_code" class="hover:bg-slate-800/30">
              <td class="p-3 font-bold font-mono text-purple-400">{{ pso.pso_code }}</td>
              <td class="p-3 text-slate-300">{{ pso.title }}</td>
              <td class="p-3 text-right font-mono">{{ pso.direct_attainment }}</td>
              <td class="p-3 text-right font-mono text-slate-400">{{ pso.indirect_attainment }}</td>
              <td class="p-3 text-right font-mono font-bold text-base text-purple-400">{{ pso.final_attainment }}</td>
              <td class="p-3 text-right font-semibold">{{ pso.percentage }}%</td>
              <td class="p-3 text-center">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                  Target Met
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Record Marks Modal -->
    <div v-if="showMarksModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="glass-card bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h4 class="text-sm font-bold text-white">Record Assessment Marks (Faculty Entry)</h4>
          <button @click="showMarksModal = false" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form @submit.prevent="submitMarks" class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Select Course</label>
            <select v-model="marksForm.course_id" @change="onCourseSelect" required
                    class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
              <option v-for="c in coursesList" :key="c.id" :value="c.id">
                {{ c.course_code }} - {{ c.course_name }}
              </option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Course Outcome (CO)</label>
              <select v-model="marksForm.course_outcome_id" required
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                <option v-for="co in activeCourseCos" :key="co.id" :value="co.id">
                  {{ co.co_code }} (Target: {{ co.target_level }})
                </option>
              </select>
            </div>
            <div>
              <label class="font-semibold text-slate-300">Assessment Type</label>
              <select v-model="marksForm.assessment_type" required
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                <option value="Internal_Test_1">Internal Test 1 (CIE)</option>
                <option value="Internal_Test_2">Internal Test 2 (CIE)</option>
                <option value="Assignment">Assignment / Quiz</option>
                <option value="End_Sem">End Semester Exam (SEE)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Total Students Attempted</label>
              <input type="number" v-model.number="marksForm.total_students_attempted" required min="1"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-emerald-400">Students Scoring ≥ 60%</label>
              <input type="number" v-model.number="marksForm.students_meeting_target" required min="0"
                     class="w-full mt-1 bg-slate-950 border border-emerald-500/50 rounded-lg px-3 py-2 text-white focus:border-emerald-400 outline-none" />
            </div>
          </div>

          <!-- Live Calculated Attainment Level Preview -->
          <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between text-xs">
            <div>
              <span class="text-slate-400">Achieved Rate:</span>
              <strong class="ml-1 text-white font-mono">{{ livePercentage }}%</strong>
            </div>
            <div>
              <span class="text-slate-400">Attainment Level:</span>
              <strong class="ml-1 text-emerald-400 font-mono text-sm">Level {{ liveLevel }} / 3.0</strong>
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
            <button type="button" @click="showMarksModal = false" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300">Cancel</button>
            <button type="submit" :disabled="submitting" class="px-4 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
              {{ submitting ? 'Recording...' : 'Save Assessment Log' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import AppIcon from './AppIcon.vue';

export default {
  name: 'AttainmentDashboard',
  components: { AppIcon },
  props: {
    attainment: {
      type: Object,
      default: () => ({}),
    },
    programId: {
      type: Number,
      default: 1,
    },
  },
  data() {
    return {
      showMarksModal: false,
      submitting: false,
      successMsg: '',
      coursesList: [],
      activeCourseCos: [],
      marksForm: {
        course_id: null,
        course_outcome_id: null,
        assessment_type: 'Internal_Test_1',
        total_students_attempted: 120,
        students_meeting_target: 95,
      },
    };
  },
  computed: {
    livePercentage() {
      const total = Math.max(1, this.marksForm.total_students_attempted || 1);
      const meeting = Math.min(total, this.marksForm.students_meeting_target || 0);
      return ((meeting / total) * 100).toFixed(1);
    },
    liveLevel() {
      const p = parseFloat(this.livePercentage);
      if (p >= 70.0) return '3.0';
      if (p >= 60.0) return '2.0';
      if (p >= 50.0) return '1.0';
      return '0.5';
    },
  },
  mounted() {
    this.fetchCourses();
  },
  methods: {
    async fetchCourses() {
      try {
        const res = await axios.get('/api/v1/courses', {
          params: { program_id: this.programId },
        });
        if (res.data && res.data.success) {
          this.coursesList = res.data.data.courses || [];
          if (this.coursesList.length > 0) {
            this.marksForm.course_id = this.coursesList[0].id;
            this.onCourseSelect();
          }
        }
      } catch (err) {
        console.error('Failed to load courses:', err);
      }
    },
    onCourseSelect() {
      const c = this.coursesList.find((item) => item.id === this.marksForm.course_id);
      if (c && c.course_outcomes && c.course_outcomes.length > 0) {
        this.activeCourseCos = c.course_outcomes;
        this.marksForm.course_outcome_id = c.course_outcomes[0].id;
      } else {
        this.activeCourseCos = [];
      }
    },
    async submitMarks() {
      this.submitting = true;
      try {
        const res = await axios.post('/api/v1/attainment/record-marks', this.marksForm);
        if (res.data && res.data.success) {
          this.successMsg = res.data.message;
          this.showMarksModal = false;
          this.$emit('updated');
        }
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to record marks');
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>
