<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-xs font-semibold border border-indigo-500/20">
          <AppIcon name="target" size="12" />
          <span>ASSESSMENT DESIGN • CO MAPPING</span>
        </div>

        <h3 class="text-xl font-bold text-white mt-2">
          Question-wise CO Blueprint Mapper
        </h3>

        <p class="text-xs text-slate-400 mt-1 max-w-2xl">
          Map every assessment question to its Course Outcome and define
          question-wise marks for measurable CO attainment.
        </p>
      </div>

      <div class="flex items-center gap-2">
        <span
          v-if="successMessage"
          class="px-3 py-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold"
        >
          ✓ {{ successMessage }}
        </span>

        <button
          @click="resetForm"
          class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-semibold transition"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Main Setup Card -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">

      <!-- Card Header -->
      <div class="px-5 py-4 border-b border-slate-800 bg-slate-900/50">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-bold text-white">
              Assessment Configuration
            </h4>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Configure the assessment before creating the blueprint.
            </p>
          </div>

          <div class="text-[10px] font-mono text-slate-500">
            STEP 01 / 02
          </div>
        </div>
      </div>

      <!-- Form -->
      <div class="p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

          <!-- Course -->
          <div class="lg:col-span-2">
            <label class="field-label">Course</label>

            <select
              v-model.number="form.course_id"
              @change="courseChanged"
              class="field-input"
            >
              <option :value="null" disabled>
                Select course
              </option>

              <option
                v-for="course in courses"
                :key="course.id"
                :value="course.id"
              >
                {{ course.course_code }} — {{ course.course_name }}
              </option>
            </select>
          </div>

          <!-- Academic Year -->
          <div>
            <label class="field-label">Academic Year</label>

            <select
              v-model.number="form.academic_year_id"
              class="field-input"
            >
              <option :value="null" disabled>
                Select year
              </option>

              <option
                v-for="year in academicYears"
                :key="year.id"
                :value="year.id"
              >
                {{ year.label }}
              </option>
            </select>
          </div>

          <!-- Assessment Type -->
          <div>
            <label class="field-label">Assessment Type</label>

            <select
              v-model="form.assessment_type"
              class="field-input"
            >
              <option value="Internal_Test_1">Internal Test 1</option>
              <option value="Internal_Test_2">Internal Test 2</option>
              <option value="Assignment">Assignment</option>
              <option value="End_Sem">End Semester</option>
              <option value="CIE">CIE</option>
              <option value="SEE">SEE</option>
            </select>
          </div>

          <!-- Assessment Name -->
          <div class="lg:col-span-3">
            <label class="field-label">Assessment Name</label>

            <input
              v-model="form.assessment_name"
              type="text"
              placeholder="e.g. Internal Test 1"
              class="field-input"
            />
          </div>

          <!-- Total Marks -->
          <div>
            <label class="field-label">Total Marks</label>

            <input
              v-model.number="form.total_marks"
              type="number"
              min="1"
              step="0.5"
              class="field-input"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Blueprint Table -->
    <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden">

      <div class="px-5 py-4 border-b border-slate-800 bg-slate-900/50 flex items-center justify-between">

        <div>
          <h4 class="text-sm font-bold text-white">
            Question → Course Outcome Mapping
          </h4>

          <p class="text-[11px] text-slate-500 mt-0.5">
            Assign one CO to each question.
          </p>
        </div>

        <div class="flex items-center gap-3">
          <div class="text-[11px] text-slate-400">
            {{ questions.length }} Questions
          </div>

          <div class="text-[11px] font-mono text-indigo-400">
            {{ calculatedMarks }} / {{ form.total_marks || 0 }}
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-if="!selectedCourse"
        class="px-6 py-16 text-center"
      >
        <div class="w-12 h-12 mx-auto rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
          <AppIcon name="courses" size="22" custom-class="text-indigo-400" />
        </div>

        <h4 class="text-sm font-bold text-white mt-4">
          Select a course first
        </h4>

        <p class="text-xs text-slate-500 mt-1">
          Course Outcomes will appear here once a course is selected.
        </p>
      </div>

      <!-- Questions -->
      <div v-else>

        <!-- Column header -->
        <div class="hidden md:grid grid-cols-[90px_1fr_150px_50px] gap-4 px-5 py-3 bg-slate-950/70 border-b border-slate-800 text-[10px] uppercase tracking-wider font-bold text-slate-500">
          <div>Question</div>
          <div>Course Outcome</div>
          <div>Maximum Marks</div>
          <div></div>
        </div>

        <div class="divide-y divide-slate-800">

          <div
            v-for="(question, index) in questions"
            :key="question.localId"
            class="grid grid-cols-1 md:grid-cols-[90px_1fr_150px_50px] gap-3 md:gap-4 px-5 py-4 items-center hover:bg-slate-800/20 transition"
          >

            <!-- Question -->
            <div>
              <div class="text-[10px] text-slate-500 md:hidden mb-1">
                QUESTION
              </div>

              <div class="w-12 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center">
                <span class="font-mono font-bold text-indigo-300">
                  Q{{ question.question_number }}
                </span>
              </div>
            </div>

            <!-- CO -->
            <div>
              <div class="text-[10px] text-slate-500 md:hidden mb-1">
                COURSE OUTCOME
              </div>

              <select
                v-model.number="question.course_outcome_id"
                class="field-input"
              >
                <option :value="null" disabled>
                  Select Course Outcome
                </option>

                <option
                  v-for="co in selectedCourse.course_outcomes || []"
                  :key="co.id"
                  :value="co.id"
                >
                  {{ co.co_code }} — {{ co.description }}
                </option>
              </select>
            </div>

            <!-- Marks -->
            <div>
              <div class="text-[10px] text-slate-500 md:hidden mb-1">
                MAXIMUM MARKS
              </div>

              <input
                v-model.number="question.max_marks"
                type="number"
                min="0.01"
                step="0.5"
                class="field-input text-center font-mono"
              />
            </div>

            <!-- Remove -->
            <div class="flex justify-end">
              <button
                v-if="questions.length > 1"
                @click="removeQuestion(index)"
                title="Remove Question"
                class="w-8 h-8 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition"
              >
                ✕
              </button>
            </div>

          </div>
        </div>

        <!-- Bottom actions -->
        <div class="px-5 py-4 border-t border-slate-800 bg-slate-950/30 flex flex-col sm:flex-row items-center justify-between gap-3">

          <button
            @click="addQuestion"
            class="w-full sm:w-auto px-3 py-2 rounded-lg border border-slate-700 bg-slate-900 hover:bg-slate-800 text-indigo-400 text-xs font-bold transition"
          >
            + Add Question
          </button>

          <div class="flex items-center gap-3 w-full sm:w-auto">

            <div
              class="text-[11px] font-semibold"
              :class="marksMatch ? 'text-emerald-400' : 'text-amber-400'"
            >
              {{ marksMatch ? '✓ Marks balanced' : `⚠ Difference: ${marksDifference}` }}
            </div>

            <button
              @click="createBlueprint"
              :disabled="saving || !canSubmit"
              class="flex-1 sm:flex-none px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition"
            >
              {{ saving ? 'Creating Blueprint...' : 'Create Blueprint →' }}
            </button>

          </div>
        </div>
      </div>
    </div>

    <!-- Created Blueprint -->
    <div
      v-if="createdBlueprint"
      class="glass-card rounded-2xl border border-emerald-500/20 overflow-hidden"
    >
      <div class="px-5 py-4 border-b border-emerald-500/10 bg-emerald-500/5">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
            <span class="text-emerald-400 text-lg">✓</span>
          </div>

          <div>
            <h4 class="text-sm font-bold text-white">
              Blueprint Created Successfully
            </h4>

            <p class="text-[11px] text-slate-500">
              {{ createdBlueprint.assessment_name }}
            </p>
          </div>
        </div>
      </div>

      <div class="p-5">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">

          <div class="stat-card">
            <span>Assessment</span>
            <strong>{{ createdBlueprint.assessment_name }}</strong>
          </div>

          <div class="stat-card">
            <span>Total Marks</span>
            <strong>{{ createdBlueprint.total_marks }}</strong>
          </div>

          <div class="stat-card">
            <span>Questions</span>
            <strong>{{ createdBlueprint.questions?.length || 0 }}</strong>
          </div>

          <div class="stat-card">
            <span>Course</span>
            <strong>{{ createdBlueprint.course?.course_code || '—' }}</strong>
          </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

          <div
            v-for="question in createdBlueprint.questions || []"
            :key="question.id"
            class="rounded-xl border border-slate-800 bg-slate-950/60 p-4"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono font-bold text-indigo-400">
                Q{{ question.question_number }}
              </span>

              <span class="text-xs font-mono text-slate-400">
                {{ question.max_marks }} marks
              </span>
            </div>

            <div class="mt-3">
              <span class="inline-flex px-2 py-1 rounded-md bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs font-bold">
                {{ question.course_outcome?.co_code || 'CO' }}
              </span>
            </div>

            <p class="text-[10px] text-slate-500 mt-2 line-clamp-2">
              {{ question.course_outcome?.description || 'Course Outcome' }}
            </p>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';
import AppIcon from './AppIcon.vue';

export default {
  name: 'AssessmentBlueprintView',

  components: {
    AppIcon,
  },

  props: {
    programId: {
      type: Number,
      default: 1,
    },
  },

  data() {
    return {
      courses: [],
      academicYears: [],

      saving: false,
      successMessage: '',
      createdBlueprint: null,

      form: {
        course_id: null,
        academic_year_id: null,
        assessment_type: 'Internal_Test_1',
        assessment_name: 'Internal Test 1',
        total_marks: 50,
      },

      questions: [],
    };
  },

  computed: {
    selectedCourse() {
      return this.courses.find(
        course => Number(course.id) === Number(this.form.course_id)
      ) || null;
    },

    calculatedMarks() {
      return this.questions.reduce(
        (sum, question) => sum + Number(question.max_marks || 0),
        0
      );
    },

    marksDifference() {
      return Math.abs(
        Number(this.form.total_marks || 0) - this.calculatedMarks
      ).toFixed(2);
    },

    marksMatch() {
      return Math.abs(
        Number(this.form.total_marks || 0) - this.calculatedMarks
      ) < 0.001;
    },

    canSubmit() {
      return (
        this.form.course_id &&
        this.form.academic_year_id &&
        this.form.assessment_type &&
        this.form.assessment_name.trim() &&
        Number(this.form.total_marks) > 0 &&
        this.questions.length > 0 &&
        this.questions.every(
          q => q.course_outcome_id && Number(q.max_marks) > 0
        ) &&
        this.marksMatch
      );
    },
  },

  mounted() {
    this.fetchCourses();
  },

  methods: {
    async fetchCourses() {
      try {
        const res = await axios.get('/api/v1/courses', {
          params: {
            program_id: this.programId,
          },
        });

        if (res.data?.success) {
          this.courses = res.data.data.courses || [];
          this.buildAcademicYears();

          if (this.courses.length > 0) {
            this.form.course_id = this.courses[0].id;
            this.courseChanged();
          }
        }
      } catch (err) {
        console.error('Failed to load courses:', err);
      }
    },

    buildAcademicYears() {
      const years = [];

      this.courses.forEach(course => {
        const yearId = course.academic_year_id;

        if (!yearId) return;

        if (!years.some(year => year.id === yearId)) {
          const label =
            course.academic_year?.name ||
            course.academic_year?.label ||
            course.academic_year?.year ||
            `Academic Year ${yearId}`;

          years.push({
            id: yearId,
            label,
          });
        }
      });

      this.academicYears = years;

      if (
        !this.form.academic_year_id &&
        this.selectedCourse?.academic_year_id
      ) {
        this.form.academic_year_id = this.selectedCourse.academic_year_id;
      }
    },

    courseChanged() {
      const course = this.selectedCourse;

      if (!course) return;

      if (course.academic_year_id) {
        this.form.academic_year_id = course.academic_year_id;
      }

      const cos = course.course_outcomes || [];

      this.questions = cos.length
        ? cos.slice(0, 5).map((co, index) => ({
            localId: Date.now() + index,
            question_number: index + 1,
            course_outcome_id: co.id,
            max_marks: 10,
          }))
        : [
            {
              localId: Date.now(),
              question_number: 1,
              course_outcome_id: null,
              max_marks: 10,
            },
          ];

      this.createdBlueprint = null;
      this.successMessage = '';
    },

    addQuestion() {
      this.questions.push({
        localId: Date.now() + Math.random(),
        question_number: this.questions.length + 1,
        course_outcome_id:
          this.selectedCourse?.course_outcomes?.[0]?.id || null,
        max_marks: 10,
      });
    },

    removeQuestion(index) {
      this.questions.splice(index, 1);

      this.questions.forEach((question, i) => {
        question.question_number = i + 1;
      });
    },

    async createBlueprint() {
      if (!this.canSubmit) {
        alert(
          'Please complete all fields and make sure question marks equal the total marks.'
        );
        return;
      }

      this.saving = true;
      this.successMessage = '';

      try {
        const payload = {
          course_id: this.form.course_id,
          academic_year_id: this.form.academic_year_id,
          assessment_type: this.form.assessment_type,
          assessment_name: this.form.assessment_name,
          total_marks: this.form.total_marks,

          questions: this.questions.map(question => ({
            question_number: question.question_number,
            course_outcome_id: question.course_outcome_id,
            max_marks: question.max_marks,
          })),
        };

        const res = await axios.post(
          '/api/v1/staff/assessments/blueprint',
          payload
        );

        if (res.data?.success) {
          this.createdBlueprint = res.data.data;
          this.successMessage = 'Blueprint saved';
        }
      } catch (err) {
        console.error('Blueprint creation failed:', err);

        const message =
          err.response?.data?.message ||
          'Failed to create assessment blueprint.';

        alert(message);
      } finally {
        this.saving = false;
      }
    },

    resetForm() {
      this.form = {
        course_id: this.courses[0]?.id || null,
        academic_year_id:
          this.courses[0]?.academic_year_id || null,
        assessment_type: 'Internal_Test_1',
        assessment_name: 'Internal Test 1',
        total_marks: 50,
      };

      this.courseChanged();
      this.createdBlueprint = null;
      this.successMessage = '';
    },
  },
};
</script>

<style scoped>
.field-label {
  display: block;
  margin-bottom: 0.375rem;
  font-size: 0.6875rem;
  font-weight: 600;
  color: rgb(203 213 225);
}

.field-input {
  width: 100%;
  border-radius: 0.625rem;
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23);
  color: white;
  padding: 0.625rem 0.75rem;
  font-size: 0.75rem;
  outline: none;
  transition: all 150ms ease;
}

.field-input:focus {
  border-color: rgb(99 102 241);
  box-shadow: 0 0 0 2px rgb(99 102 241 / 0.1);
}

.field-input option {
  background: rgb(15 23 42);
  color: white;
}

.stat-card {
  border: 1px solid rgb(30 41 59);
  background: rgb(2 6 23 / 0.6);
  border-radius: 0.75rem;
  padding: 0.75rem;
}

.stat-card span {
  display: block;
  font-size: 0.625rem;
  color: rgb(100 116 139);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-card strong {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: rgb(226 232 240);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>