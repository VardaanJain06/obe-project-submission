<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-xs font-semibold border border-indigo-500/20">
          <AppIcon name="courses" size="12" />
          <span>Criterion 2 & 3 • Course Definition & Outcomes</span>
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          Courses Catalog & Course Outcomes (COs) Management
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Define accredited syllabus courses, specify Bloom taxonomy levels, and configure target attainment threshold levels (CO1 to CO6).
        </p>
      </div>

      <div>
        <button @click="openCreateCourseModal"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Add New Course
        </button>
      </div>
    </div>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="course in courses" :key="course.id"
           class="glass-card rounded-xl p-5 border border-slate-800 flex flex-col justify-between hover:border-indigo-500/40 transition">
        <div>
          <div class="flex items-start justify-between gap-2">
            <div>
              <span class="text-xs font-mono font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">
                {{ course.course_code }}
              </span>
              <span class="ml-2 text-[10px] text-slate-400">Sem {{ course.semester }} • {{ course.credits }} Credits</span>
            </div>
            <button @click="deleteCourse(course)" title="Delete Course" class="text-slate-500 hover:text-rose-400 transition text-xs">
              ✕
            </button>
          </div>

          <h4 class="text-sm font-bold text-white mt-2.5 leading-snug">
            {{ course.course_name }}
          </h4>

          <div class="mt-3 text-[11px] text-slate-400 flex items-center gap-2">
            <span>Hours: {{ course.lecture_hours }}L + {{ course.practical_hours }}P</span>
            <span>•</span>
            <span class="text-indigo-300 font-semibold">{{ course.course_outcomes?.length || 0 }} Course Outcomes</span>
          </div>

          <!-- CO List Preview -->
          <div class="mt-3 pt-3 border-t border-slate-800/80 space-y-1.5">
            <div v-for="co in course.course_outcomes" :key="co.id"
                 class="flex items-center justify-between text-[11px] p-1.5 rounded bg-slate-950/60 border border-slate-800">
              <span class="font-mono font-bold text-indigo-300">{{ co.co_code }}</span>
              <span class="text-[10px] px-1.5 rounded bg-slate-800 text-slate-300">{{ co.bloom_level }}</span>
              <span class="text-[10px] text-emerald-400 font-mono">Target {{ co.target_level }}</span>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
          <button @click="openAddCoModal(course)" class="text-indigo-400 hover:text-indigo-300 font-semibold text-[11px] flex items-center gap-1">
            + Add CO Definition
          </button>
          <span class="text-[10px] text-slate-500">{{ course.course_type || 'Core' }}</span>
        </div>
      </div>
    </div>

    <!-- Create Course Modal -->
    <div v-if="showCourseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="glass-card bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h4 class="text-sm font-bold text-white">Create New Course in Curriculum</h4>
          <button @click="showCourseModal = false" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form @submit.prevent="saveCourse" class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Course Code</label>
              <input type="text" v-model="courseForm.course_code" required placeholder="e.g. CS603"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Semester (1 - 8)</label>
              <input type="number" v-model.number="courseForm.semester" required min="1" max="8"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div>
            <label class="font-semibold text-slate-300">Course Title / Name</label>
            <input type="text" v-model="courseForm.course_name" required placeholder="e.g. Distributed Cloud Computing"
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Credits</label>
              <input type="number" step="0.5" v-model.number="courseForm.credits" required
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Lecture (L)</label>
              <input type="number" v-model.number="courseForm.lecture_hours"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Practical (P)</label>
              <input type="number" v-model.number="courseForm.practical_hours"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
            <button type="button" @click="showCourseModal = false" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold">
              {{ saving ? 'Saving...' : 'Create Course' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add CO Modal -->
    <div v-if="showCoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="glass-card bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h4 class="text-sm font-bold text-white">Add CO to {{ activeCourse?.course_code }}</h4>
          <button @click="showCoModal = false" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <form @submit.prevent="saveCo" class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">CO Code</label>
              <input type="text" v-model="coForm.co_code" required placeholder="e.g. CO6"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Bloom Taxonomy</label>
              <select v-model="coForm.bloom_level" required
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                <option value="Remember">Remember</option>
                <option value="Understand">Understand</option>
                <option value="Apply">Apply</option>
                <option value="Analyze">Analyze</option>
                <option value="Evaluate">Evaluate</option>
                <option value="Create">Create</option>
              </select>
            </div>
          </div>

          <div>
            <label class="font-semibold text-slate-300">Course Outcome Description</label>
            <textarea v-model="coForm.description" required rows="2" placeholder="Demonstrate ability to..."
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Target Pass % (Students)</label>
              <input type="number" v-model.number="coForm.target_percentage" required placeholder="60"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Target Level (1 - 3)</label>
              <input type="number" step="0.1" v-model.number="coForm.target_level" required placeholder="2.5"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
            <button type="button" @click="showCoModal = false" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300">Cancel</button>
            <button type="submit" :disabled="saving" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold">
              {{ saving ? 'Adding...' : 'Save CO Definition' }}
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
  name: 'CoursesManagementView',
  components: { AppIcon },
  props: {
    programId: {
      type: Number,
      default: 1,
    },
  },
  data() {
    return {
      courses: [],
      showCourseModal: false,
      showCoModal: false,
      activeCourse: null,
      saving: false,
      courseForm: {
        program_id: 1,
        course_code: '',
        course_name: '',
        semester: 5,
        credits: 4.0,
        lecture_hours: 3,
        practical_hours: 2,
        course_type: 'Core',
      },
      coForm: {
        co_code: 'CO6',
        description: '',
        target_percentage: 65,
        target_level: 2.5,
        bloom_level: 'Apply',
      },
    };
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
          this.courses = res.data.data.courses || [];
        }
      } catch (err) {
        console.error('Failed to load courses:', err);
      }
    },
    openCreateCourseModal() {
      this.courseForm = {
        program_id: this.programId,
        course_code: 'CS' + Math.floor(500 + Math.random() * 400),
        course_name: '',
        semester: 5,
        credits: 4.0,
        lecture_hours: 3,
        practical_hours: 2,
        course_type: 'Core',
      };
      this.showCourseModal = true;
    },
    async saveCourse() {
      this.saving = true;
      try {
        await axios.post('/api/v1/courses', this.courseForm);
        this.showCourseModal = false;
        this.fetchCourses();
        this.$emit('updated');
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to create course');
      } finally {
        this.saving = false;
      }
    },
    openAddCoModal(course) {
      this.activeCourse = course;
      const count = (course.course_outcomes?.length || 0) + 1;
      this.coForm = {
        co_code: `CO${count}`,
        description: `Apply advanced principles of ${course.course_name}.`,
        target_percentage: 65,
        target_level: 2.5,
        bloom_level: 'Analyze',
      };
      this.showCoModal = true;
    },
    async saveCo() {
      if (!this.activeCourse) return;
      this.saving = true;
      try {
        await axios.post(`/api/v1/courses/${this.activeCourse.id}/co`, this.coForm);
        this.showCoModal = false;
        this.fetchCourses();
        this.$emit('updated');
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to add CO');
      } finally {
        this.saving = false;
      }
    },
    async deleteCourse(course) {
      if (!confirm(`Are you sure you want to delete ${course.course_code}?`)) return;
      try {
        await axios.delete(`/api/v1/courses/${course.id}`);
        this.fetchCourses();
        this.$emit('updated');
      } catch (err) {
        alert(err.response?.data?.message || 'Delete failed');
      }
    },
  },
};
</script>
