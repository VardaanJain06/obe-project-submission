<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-start justify-between gap-4">
      <div>
        <div
          class="inline-flex items-center gap-2 rounded-lg border border-indigo-500/30
                 bg-indigo-500/10 px-3 py-1.5 text-xs font-semibold uppercase
                 tracking-wider text-indigo-300"
        >
          <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
          Student Assessment Data
        </div>

        <h1 class="mt-3 text-3xl font-bold text-white">
          Bulk Marks Upload
        </h1>

        <p class="mt-2 max-w-2xl text-sm text-slate-400">
          Upload student-wise assessment marks using a CSV file and automatically
          map them to the configured assessment questions.
        </p>
      </div>

      <button
        @click="resetForm"
        class="rounded-lg border border-slate-700 bg-slate-900/70 px-4 py-2
               text-sm font-medium text-slate-300 transition hover:border-indigo-500/50
               hover:text-white"
      >
        Reset
      </button>
    </div>


    <!-- Assessment Selection -->
    <div
      class="rounded-2xl border border-slate-800 bg-slate-900/60
             shadow-xl shadow-black/10"
    >
      <div class="border-b border-slate-800 px-6 py-5">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-white">
              Select Assessment
            </h2>
            <p class="mt-1 text-xs text-slate-500">
              Choose the assessment whose student marks you want to upload.
            </p>
          </div>

          <div
            v-if="selectedBatch"
            class="rounded-lg border border-indigo-500/20 bg-indigo-500/10
                   px-3 py-1.5 text-xs text-indigo-300"
          >
            Batch #{{ selectedBatch.id }}
          </div>
        </div>
      </div>

      <div class="grid gap-5 p-6 md:grid-cols-2">

        <!-- Assessment Batch -->
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-400">
            Assessment
          </label>

          <select
            v-model="selectedBatchId"
            @change="loadBlueprint"
            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3
                   text-sm text-white outline-none transition
                   focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
          >
            <option value="">Select assessment...</option>

            <option
              v-for="batch in batches"
              :key="batch.id"
              :value="batch.id"
            >
              {{ batch.assessment_name }}
              — {{ batch.course?.course_code || 'Course' }}
            </option>
          </select>
        </div>

        <!-- Course -->
        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-400">
            Course
          </label>

          <div
            class="rounded-xl border border-slate-800 bg-slate-950 px-4 py-3"
          >
            <div v-if="selectedBatch">
              <div class="text-sm font-semibold text-white">
                {{ selectedBatch.course?.course_code || '—' }}
              </div>

              <div class="mt-1 text-xs text-slate-500">
                {{ selectedBatch.course?.course_name || 'Course information unavailable' }}
              </div>
            </div>

            <div v-else class="text-sm text-slate-600">
              Select an assessment first
            </div>
          </div>
        </div>

      </div>
    </div>


    <!-- Blueprint Preview -->
    <div
      v-if="selectedBatch && questions.length"
      class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl"
    >
      <div class="border-b border-slate-800 px-6 py-5">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-white">
              Assessment Blueprint
            </h2>

            <p class="mt-1 text-xs text-slate-500">
              Marks in your CSV must match these question numbers.
            </p>
          </div>

          <div class="text-right">
            <div class="text-xl font-bold text-indigo-400">
              {{ selectedBatch.total_marks }}
            </div>
            <div class="text-[10px] uppercase tracking-wider text-slate-500">
              Total Marks
            </div>
          </div>
        </div>
      </div>

      <div class="grid gap-3 p-6 sm:grid-cols-2 lg:grid-cols-5">

        <div
          v-for="question in questions"
          :key="question.id"
          class="rounded-xl border border-slate-800 bg-slate-950/70 p-4"
        >
          <div class="flex items-center justify-between">
            <span
              class="rounded-md bg-indigo-500/10 px-2 py-1 text-xs
                     font-bold text-indigo-300"
            >
              Q{{ question.question_number }}
            </span>

            <span class="text-xs font-semibold text-slate-400">
              {{ question.max_marks }} marks
            </span>
          </div>

          <div class="mt-3 text-xs leading-relaxed text-slate-500">
            {{ question.course_outcome?.co_code || 'CO' }}
          </div>
        </div>

      </div>
    </div>


    <!-- CSV Upload -->
    <div
      class="rounded-2xl border border-slate-800 bg-slate-900/60 shadow-xl"
    >
      <div class="border-b border-slate-800 px-6 py-5">
        <h2 class="text-lg font-semibold text-white">
          Upload Student Marks
        </h2>

        <p class="mt-1 text-xs text-slate-500">
          CSV format: student_id,Q1,Q2,Q3,Q4,Q5...
        </p>
      </div>

      <div class="p-6">

        <!-- Drop Zone -->
        <label
          class="group flex cursor-pointer flex-col items-center justify-center
                 rounded-2xl border border-dashed border-slate-700
                 bg-slate-950/60 px-6 py-12 text-center transition
                 hover:border-indigo-500/60 hover:bg-indigo-500/5"
        >

          <input
            ref="fileInput"
            type="file"
            accept=".csv,text/csv"
            class="hidden"
            @change="handleFile"
          />

          <div
            class="flex h-14 w-14 items-center justify-center rounded-2xl
                   border border-indigo-500/20 bg-indigo-500/10
                   text-2xl text-indigo-400 transition
                   group-hover:scale-105"
          >
            ↑
          </div>

          <div class="mt-4 text-sm font-semibold text-white">
            {{ file ? file.name : 'Choose CSV file' }}
          </div>

          <div class="mt-2 text-xs text-slate-500">
            Click to browse • CSV files only
          </div>

          <div
            v-if="file"
            class="mt-4 rounded-lg border border-emerald-500/20
                   bg-emerald-500/10 px-3 py-2 text-xs text-emerald-300"
          >
            {{ formatFileSize(file.size) }}
          </div>

        </label>


        <!-- CSV Format -->
        <div
          class="mt-5 rounded-xl border border-slate-800 bg-slate-950/70 p-4"
        >
          <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">
            Required CSV format
          </div>

          <pre
            class="mt-3 overflow-x-auto rounded-lg border border-slate-800
                   bg-black/30 p-4 text-xs leading-relaxed text-indigo-300"
          >student_id,Q1,Q2,Q3,Q4,Q5
STU001,8,7,9,6,8
STU002,7,8,8,9,7
STU003,9,6,7,8,9</pre>
        </div>


        <!-- Error -->
        <div
          v-if="errorMessage"
          class="mt-5 rounded-xl border border-red-500/20
                 bg-red-500/10 px-4 py-3 text-sm text-red-300"
        >
          {{ errorMessage }}
        </div>


        <!-- Success -->
        <div
          v-if="successMessage"
          class="mt-5 rounded-xl border border-emerald-500/20
                 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300"
        >
          {{ successMessage }}
        </div>


        <!-- Upload Button -->
        <div class="mt-6 flex justify-end">
          <button
            @click="uploadMarks"
            :disabled="!file || !selectedBatchId || uploading"
            class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-semibold
                   text-white shadow-lg shadow-indigo-600/20 transition
                   hover:bg-indigo-500 disabled:cursor-not-allowed
                   disabled:opacity-40"
          >
            <span v-if="uploading">
              Uploading...
            </span>

            <span v-else>
              Upload Marks →
            </span>
          </button>
        </div>

      </div>
    </div>


    <!-- Result -->
    <div
      v-if="uploadResult"
      class="rounded-2xl border border-emerald-500/20
             bg-emerald-500/5 p-6"
    >
      <div class="flex items-center gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-xl
                 bg-emerald-500/10 text-emerald-400"
        >
          ✓
        </div>

        <div>
          <h3 class="font-semibold text-white">
            Marks imported successfully
          </h3>

          <p class="mt-1 text-xs text-slate-500">
            Assessment data has been saved to the system.
          </p>
        </div>
      </div>

      <div class="mt-5 grid gap-4 sm:grid-cols-3">

        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
          <div class="text-2xl font-bold text-white">
            {{ uploadResult.students_imported || 0 }}
          </div>
          <div class="mt-1 text-xs text-slate-500">
            Students Imported
          </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
          <div class="text-2xl font-bold text-white">
            {{ uploadResult.marks_imported || 0 }}
          </div>
          <div class="mt-1 text-xs text-slate-500">
            Marks Imported
          </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
          <div class="text-2xl font-bold text-indigo-400">
            #{{ uploadResult.assessment_batch_id || selectedBatchId }}
          </div>
          <div class="mt-1 text-xs text-slate-500">
            Assessment Batch
          </div>
        </div>

      </div>
    </div>

  </div>
</template>


<script>
import axios from 'axios';

export default {
  name: 'StudentMarksUploadView',

  props: {
    programId: {
      type: [Number, String],
      default: null,
    },
  },

  data() {
    return {
      batches: [],
      selectedBatchId: '',
      selectedBatch: null,
      questions: [],

      file: null,

      uploading: false,
      errorMessage: '',
      successMessage: '',
      uploadResult: null,
    };
  },

  mounted() {
    this.loadBatches();
  },

  methods: {

    async loadBatches() {
      try {
        this.errorMessage = '';

        const response = await axios.get('/api/v1/courses', {
          params: {
            program_id: this.programId,
          },
        });

        const courses = response.data?.data?.courses || [];

        /*
         * The current backend does not expose a dedicated
         * assessment-batches listing endpoint.
         *
         * So we discover available assessments from the
         * course records if they are embedded.
         */

        const discovered = [];

        courses.forEach(course => {
          if (Array.isArray(course.assessment_batches)) {
            course.assessment_batches.forEach(batch => {
              discovered.push({
                ...batch,
                course: course,
              });
            });
          }
        });

        this.batches = discovered;

        /*
         * If the backend eventually exposes /assessment-batches,
         * this component can be switched to that endpoint directly.
         */
      } catch (error) {
        console.error(error);

        this.errorMessage =
          error.response?.data?.message ||
          'Unable to load assessment batches.';
      }
    },


    async loadBlueprint() {
      this.file = null;
      this.questions = [];
      this.selectedBatch = null;
      this.errorMessage = '';
      this.successMessage = '';
      this.uploadResult = null;

      if (!this.selectedBatchId) {
        return;
      }

      try {
        const response = await axios.get(
          `/api/v1/staff/assessments/${this.selectedBatchId}/blueprint`
        );

        const batch = response.data?.data;

        if (!batch) {
          throw new Error('Assessment blueprint not found.');
        }

        this.selectedBatch = batch;
        this.questions = batch.questions || [];

      } catch (error) {
        console.error(error);

        this.errorMessage =
          error.response?.data?.message ||
          'Unable to load assessment blueprint.';
      }
    },


    handleFile(event) {
      const selectedFile = event.target.files?.[0];

      this.errorMessage = '';
      this.successMessage = '';
      this.uploadResult = null;

      if (!selectedFile) {
        this.file = null;
        return;
      }

      if (!selectedFile.name.toLowerCase().endsWith('.csv')) {
        this.errorMessage = 'Please select a valid CSV file.';
        this.file = null;
        return;
      }

      this.file = selectedFile;
    },


    async uploadMarks() {
      if (!this.file) {
        this.errorMessage = 'Please select a CSV file first.';
        return;
      }

      if (!this.selectedBatchId) {
        this.errorMessage = 'Please select an assessment first.';
        return;
      }

      this.uploading = true;
      this.errorMessage = '';
      this.successMessage = '';
      this.uploadResult = null;

      try {
        const formData = new FormData();

        formData.append('file', this.file);

        const response = await axios.post(
          `/api/v1/staff/assessments/${this.selectedBatchId}/marks/upload`,
          formData,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          }
        );

        const data = response.data?.data || {};

        this.uploadResult = data;

        this.successMessage =
          response.data?.message ||
          'Student marks imported successfully.';

        this.file = null;

        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = '';
        }

      } catch (error) {
        console.error(error);

        this.errorMessage =
          error.response?.data?.message ||
          'Marks upload failed. Please check your CSV format.';

      } finally {
        this.uploading = false;
      }
    },


    resetForm() {
      this.selectedBatchId = '';
      this.selectedBatch = null;
      this.questions = [];
      this.file = null;
      this.errorMessage = '';
      this.successMessage = '';
      this.uploadResult = null;

      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },


    formatFileSize(bytes) {
      if (!bytes) return '0 KB';

      const kb = bytes / 1024;

      if (kb < 1024) {
        return `${kb.toFixed(1)} KB`;
      }

      return `${(kb / 1024).toFixed(1)} MB`;
    },
  },
};
</script>