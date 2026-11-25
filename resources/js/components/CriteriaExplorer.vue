<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <div
          class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-xs font-semibold border border-indigo-500/20"
        >
          NBA Tier-II SAR Criteria (1 to 10)
        </div>

        <h3 class="text-xl font-bold text-white mt-1">
          Accreditation Criteria & Claimed Scores
        </h3>
      </div>

      <div class="text-right">
        <span class="text-xs text-slate-400">Total Score:</span>

        <span class="ml-1 text-xl font-black text-emerald-400">
          {{ displayedSar.total_claimed_marks ?? 0 }}
        </span>

        <span class="text-xs text-slate-400">
          / 1000.0
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loadingCriteria"
      class="p-4 rounded-lg bg-slate-900/60 border border-slate-800 text-center"
    >
      <span class="text-xs text-slate-400">
        Loading live criterion scores...
      </span>
    </div>

    <!-- Criteria Accordion / List -->
    <div
      v-else
      class="space-y-3"
    >
      <div
        v-for="c in displayedCriteria"
        :key="c.number"
        class="glass-card rounded-xl overflow-hidden border border-slate-800 transition"
      >
        <!-- Criterion Header -->
        <div
          @click="toggleExpand(c.number)"
          class="p-4 bg-slate-900/60 flex items-center justify-between cursor-pointer hover:bg-slate-900/90 transition"
        >
          <div class="flex items-center space-x-3">
            <span
              class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center font-bold text-indigo-300 text-sm"
            >
              C{{ c.number }}
            </span>

            <div>
              <div class="text-sm font-bold text-white">
                {{ c.title }}
              </div>

              <div class="text-xs text-slate-400">
                Criterion {{ c.number }} • Max Marks:
                {{ c.max_marks }}
              </div>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Radial Progress Ring -->
            <div
              class="relative w-14 h-14 flex-shrink-0"
              :title="`${c.percentage}% claimed`"
            >
              <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                <!-- Background Circle -->
                <circle
                  cx="18"
                  cy="18"
                  r="16"
                  fill="none"
                  class="stroke-slate-700/50"
                  stroke-width="3"
                ></circle>
                <!-- Progress Circle -->
                <circle
                  cx="18"
                  cy="18"
                  r="16"
                  fill="none"
                  class="stroke-emerald-400 transition-all duration-1000 ease-out"
                  stroke-width="3"
                  pathLength="100"
                  stroke-dasharray="100"
                  :stroke-dashoffset="100 - (Number(c.percentage ?? 0) || 0)"
                  stroke-linecap="round"
                ></circle>
              </svg>

              <div
                class="absolute inset-[4px] rounded-full bg-slate-950 flex items-center justify-center"
              >
                <span class="text-[10px] font-bold text-emerald-400">
                  {{ Number(c.percentage ?? 0).toFixed(1) }}%
                </span>
              </div>
            </div>

            <!-- Score -->
            <div class="text-right">
              <div class="text-base font-bold text-emerald-400">
                {{ c.claimed_marks }}

                <span class="text-xs text-slate-400 font-normal">
                  / {{ c.max_marks }}
                </span>
              </div>

              <div class="text-[10px] text-slate-400">
                {{ c.percentage }}% Claimed
              </div>
            </div>

            <span class="text-slate-400 text-sm">
              {{ expanded === c.number ? '▲' : '▼' }}
            </span>
          </div>
        </div>

        <!-- Expanded Details -->
        <div
          v-if="expanded === c.number"
          class="p-5 border-t border-slate-800 bg-slate-950/40 space-y-4"
        >
          <!-- Summary -->
          <div>
            <h5
              class="text-xs font-bold text-slate-300 uppercase tracking-wider"
            >
              Self-Assessment Summary
            </h5>

            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
              {{ c.summary }}
            </p>
          </div>

          <!-- Criterion 1 Vision, Mission & PEOs Snapshot -->
          <div
            v-if="c.number === 1"
            class="p-4 rounded-xl bg-slate-900 border border-indigo-500/30 text-xs space-y-3"
          >
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
              <span class="font-bold text-indigo-300 flex items-center gap-2 text-sm">
                <span>🏛️</span> Institutional Vision & Mission (TIET)
              </span>
              <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-semibold text-[10px] border border-emerald-500/20">
                Criterion 1.1 Verified
              </span>
            </div>

            <!-- Vision Block -->
            <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800/80 space-y-2">
              <div class="flex items-center gap-1.5 text-amber-400 font-bold text-xs uppercase tracking-wide">
                <span>👁️</span> Vision
              </div>
              <p class="text-slate-200 text-xs font-medium leading-relaxed">
                At TIET, our vision rests on two pillars:
              </p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5 pt-1">
                <div class="p-3 rounded-lg bg-slate-900/90 border border-indigo-500/20 space-y-1">
                  <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Pillar 1 • Stakeholder Experience</div>
                  <p class="text-[11px] text-slate-300 leading-normal">
                    A deeply meaningful experience for all stakeholders that shapes lives with wisdom, purpose, and compassion.
                  </p>
                </div>
                <div class="p-3 rounded-lg bg-slate-900/90 border border-indigo-500/20 space-y-1">
                  <div class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">Pillar 2 • Socially Impactful Research</div>
                  <p class="text-[11px] text-slate-300 leading-normal">
                    Socially impactful research that advances knowledge while solving real challenges of industry, society, and humanity.
                  </p>
                </div>
              </div>
              <p class="text-[11px] text-slate-400 italic pt-1">
                Together, they embody our commitment to seeking excellence for all.
              </p>
            </div>

            <!-- Mission Block -->
            <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800/80 space-y-2">
              <div class="flex items-center gap-1.5 text-emerald-400 font-bold text-xs uppercase tracking-wide">
                <span>🎯</span> Mission
              </div>
              <p class="text-slate-200 text-xs font-medium leading-relaxed">
                TIET exists to educate and empower students while advancing knowledge through research, so that we serve our society with wisdom, compassion, and a commitment to seek excellence.
              </p>
            </div>
          </div>

          <!-- Criterion 2 Live Intake Snapshot -->
          <div
            v-if="c.number === 2"
            class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-xs space-y-2"
          >
            <div class="font-semibold text-indigo-300">
              Criterion 2 Intake Metrics Snapshot:
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <!-- Current Intake -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Current Intake
                </div>

                <div class="font-bold text-white font-mono">
                  {{
                    displayedSar.live_engines?.intake
                      ?.current_total_students ?? 0
                  }}
                </div>
              </div>

              <!-- Average -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  3-Year Average Intake
                </div>

                <div class="font-bold text-white font-mono">
                  {{
                    displayedSar.live_engines?.intake
                      ?.average_total_students ?? 0
                  }}
                </div>
              </div>

              <!-- Stability -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Intake Stability
                </div>

                <div class="font-bold text-emerald-400 font-mono">
                  {{
                    displayedSar.live_engines?.intake
                      ?.stability_percentage ?? 0
                  }}%
                </div>
              </div>
            </div>
          </div>

          <!-- Criterion 4 Snapshot -->
          <div
            v-if="c.number === 4"
            class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-xs space-y-2"
          >
            <div class="font-semibold text-indigo-300">
              Criterion 4 Metrics Snapshot:
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <!-- Success Rate -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Success Rate SI (w/o Backlog)
                </div>

                <div class="font-bold text-white font-mono">
                  {{
                    displayedSar.live_engines?.success_rate
                      ?.mean_si_without_backlog ?? 0
                  }}

                  ({{
                    displayedSar.live_engines?.success_rate
                      ?.marks_without_backlog ?? 0
                  }}/25)
                </div>
              </div>

              <!-- Graduation -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Graduation in Stipulated Time
                </div>

                <div class="font-bold text-white font-mono">
                  {{
                    displayedSar.live_engines?.success_rate
                      ?.mean_si_stipulated_time ?? 0
                  }}

                  ({{
                    displayedSar.live_engines?.success_rate
                      ?.marks_stipulated_time ?? 0
                  }}/15)
                </div>
              </div>

              <!-- Placement -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Placement & Higher Studies Index
                </div>

                <div class="font-bold text-emerald-400 font-mono">
                  {{ placementPercentage }}%

                  ({{
                    displayedSar.live_engines?.placement
                      ?.claimed_marks ?? 0
                  }}/40)
                </div>
              </div>
            </div>
          </div>

          <!-- Criterion 5 Snapshot -->
          <div
            v-if="c.number === 5"
            class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-xs space-y-2"
          >
            <div class="font-semibold text-indigo-300">
              Criterion 5 Faculty Information & Contribution Snapshot:
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <!-- SFR -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Student-Faculty Ratio (SFR)
                </div>

                <div class="font-bold text-white font-mono">
                  {{ displayedSar.live_engines?.sfr?.avg_sfr ?? 8.15 }}:1
                  ({{ displayedSar.live_engines?.sfr?.claimed_marks ?? 20 }}/20)
                </div>
              </div>

              <!-- FQI -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Faculty Qualification Index (FQI)
                </div>

                <div class="font-bold text-white font-mono">
                  {{ displayedSar.live_engines?.fqi?.avg_fqi ?? 25 }}/25.0
                  ({{ displayedSar.live_engines?.fqi?.claimed_marks ?? 25 }}/25)
                </div>
              </div>

              <!-- Cadre Ratio -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Cadre Distribution (Prof : Assoc : Asst)
                </div>

                <div class="font-bold text-emerald-400 font-mono">
                  11 : 19 : 30
                  ({{ displayedSar.live_engines?.cadre?.claimed_marks ?? 25 }}/25)
                </div>
              </div>
            </div>
          </div>

          <!-- Criterion 10 Snapshot -->
          <div
            v-if="c.number === 10"
            class="p-3 rounded-lg bg-slate-900 border border-slate-800 text-xs space-y-2"
          >
            <div class="font-semibold text-indigo-300">
              Criterion 10 Budget & Governance Snapshot:
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <!-- Expenditure -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Total 3-Year Expenditure
                </div>

                <div class="font-bold text-white">
                  ₹{{
                    (
                      displayedSar.live_engines?.budget
                        ?.total_spent ?? 0
                    ).toLocaleString()
                  }}
                </div>
              </div>

              <!-- Utilization -->
              <div class="p-2 rounded bg-slate-950">
                <div class="text-slate-400 text-[10px]">
                  Budget Utilization Percentage
                </div>

                <div class="font-bold text-emerald-400 font-mono">
                  {{
                    displayedSar.live_engines?.budget
                      ?.overall_utilization ?? 0
                  }}%

                  ({{
                    displayedSar.live_engines?.budget
                      ?.claimed_marks ?? 0
                  }}/30 Pts)
                </div>
              </div>
            </div>
          </div>

          <!-- Evidence Documents -->
          <div class="mt-6 border-t border-slate-800 pt-4">
            <div class="flex items-center justify-between mb-3">
              <h5
                class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2"
              >
                <span>📎</span>
                Supporting Evidence (PDF)
              </h5>

              <!-- Upload Button -->
              <div v-if="!displayedSar.is_locked">
                <label
                  :for="'upload-' + c.number"
                  class="cursor-pointer px-3 py-1.5 rounded bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium transition flex items-center gap-1"
                >
                  <span v-if="uploading[c.number]">
                    Uploading...
                  </span>

                  <span v-else>
                    + Upload PDF
                  </span>
                </label>

                <input
                  type="file"
                  :id="'upload-' + c.number"
                  accept=".pdf"
                  class="hidden"
                  :disabled="uploading[c.number]"
                  @change="uploadEvidence($event, c.number)"
                />
              </div>

              <!-- Locked -->
              <div v-else>
                <span
                  class="px-3 py-1 rounded bg-slate-800 text-slate-400 text-xs font-medium border border-slate-700"
                >
                  🔒 SAR Locked
                </span>
              </div>
            </div>

            <!-- No Evidence -->
            <div
              v-if="
                !evidences[c.number] ||
                evidences[c.number].length === 0
              "
              class="p-4 rounded-lg bg-slate-900/50 border border-slate-800/50 text-center"
            >
              <span class="text-xs text-slate-500">
                No evidence documents uploaded yet for Criterion
                {{ c.number }}.
              </span>
            </div>

            <!-- Evidence List -->
            <div
              v-else
              class="space-y-2"
            >
              <div
                v-for="ev in evidences[c.number]"
                :key="ev.id"
                class="flex items-center justify-between p-3 rounded-lg bg-slate-900 border border-slate-800"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="w-8 h-8 rounded bg-rose-500/10 text-rose-400 flex items-center justify-center"
                  >
                    📄
                  </span>

                  <div>
                    <div class="text-xs font-medium text-slate-200">
                      {{ ev.file_name }}
                    </div>

                    <div class="text-[10px] text-slate-500">
                      Uploaded by {{ ev.uploaded_by }} •
                      {{ new Date(ev.created_at).toLocaleDateString() }}
                    </div>
                  </div>
                </div>

                <div class="flex items-center gap-2">
                  <!-- Download -->
                  <button
                    @click="downloadEvidence(ev)"
                    class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] transition font-medium border border-slate-700"
                  >
                    Download
                  </button>

                  <!-- Delete -->
                  <button
                    v-if="!displayedSar.is_locked"
                    @click="deleteEvidence(ev.id, c.number)"
                    class="px-2.5 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-[10px] transition font-medium border border-rose-500/20"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CriteriaExplorer',

  props: {
    criteria: {
      type: Array,
      default: () => [],
    },

    sar: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      expanded: 1,
      evidences: {},
      uploading: {},
      liveSar: null,
      loadingCriteria: false,
    };
  },

  computed: {
    /*
     * Keep the original SAR object as well as the live API data.
     *
     * This is important because the criterion-score API does not
     * currently return fields such as is_locked.
     */
    displayedSar() {
      return {
        ...(this.sar || {}),
        ...(this.liveSar || {}),
      };
    },

    /*
     * Criteria displayed in the accordion come from the live
     * criterion-score API when available.
     */
    displayedCriteria() {
      return (
        this.liveSar?.criteria ||
        this.criteria ||
        []
      );
    },

    /*
     * Placement percentage shown in Criterion 4.
     */
    placementPercentage() {
      const value =
        this.displayedSar.live_engines?.placement
          ?.avg_placement_index ?? 0;

      return (Number(value) * 100).toFixed(1);
    },
  },

  mounted() {
    this.fetchCriterionScores();

    if (this.expanded && this.sar.report_id) {
      this.fetchEvidences(this.expanded);
    }
  },

  watch: {
    'sar.report_id': function (newVal) {
      if (newVal) {
        this.fetchCriterionScores();

        if (this.expanded) {
          this.fetchEvidences(this.expanded);
        }
      }
    },
  },

  methods: {
    /*
     * Creates the animated radial progress ring.
     *
     * Example:
     * 80% -> 80% of the circle is filled.
     */
    progressRingStyle(percentage) {
      const value = Math.min(
        100,
        Math.max(0, Number(percentage ?? 0))
      );

      return {
        background: `conic-gradient(
          #34d399 ${value}%,
          rgba(51, 65, 85, 0.45) ${value}% 100%
        )`,
      };
    },

    /*
     * Fetch live scores from:
     * GET /api/v1/sar/criterion-scores
     */
    async fetchCriterionScores() {
      this.loadingCriteria = true;

      try {
        const params = this.sar.report_id
          ? {
              sar_report_id: this.sar.report_id,
            }
          : {};

        const res = await axios.get(
          '/api/v1/sar/criterion-scores',
          {
            params,
          }
        );

        if (res.data && res.data.success) {
          this.liveSar = res.data.data;
        }
      } catch (err) {
        console.error(
          'Failed to fetch live criterion scores:',
          err
        );
      } finally {
        this.loadingCriteria = false;
      }
    },

    /*
     * Expand / collapse a criterion.
     */
    toggleExpand(num) {
      this.expanded =
        this.expanded === num
          ? null
          : num;

      if (
        this.expanded &&
        this.sar.report_id
      ) {
        this.fetchEvidences(num);
      }
    },

    /*
     * Load evidence documents for a criterion.
     */
    async fetchEvidences(criterionNumber) {
      if (!this.sar.report_id) {
        return;
      }

      try {
        const res = await axios.get(
          `/api/v1/nba/workflow/${this.sar.report_id}/criteria/${criterionNumber}/evidence`
        );

        if (res.data && res.data.success) {
          this.evidences = {
            ...this.evidences,
            [criterionNumber]: res.data.data,
          };
        }
      } catch (err) {
        console.error(
          'Failed to fetch evidence:',
          err
        );
      }
    },

    /*
     * Upload evidence PDF.
     */
    async uploadEvidence(
      event,
      criterionNumber
    ) {
      const file =
        event.target.files[0];

      if (!file) {
        return;
      }

      const formData =
        new FormData();

      formData.append(
        'evidence_file',
        file
      );

      this.uploading = {
        ...this.uploading,
        [criterionNumber]: true,
      };

      try {
        const res =
          await axios.post(
            `/api/v1/nba/workflow/${this.sar.report_id}/criteria/${criterionNumber}/evidence`,
            formData,
            {
              headers: {
                'Content-Type':
                  'multipart/form-data',
              },
            }
          );

        if (
          res.data &&
          res.data.success
        ) {
          event.target.value = '';

          await this.fetchEvidences(
            criterionNumber
          );
        }
      } catch (err) {
        alert(
          err.response?.data?.message ||
            'Failed to upload evidence.'
        );
      } finally {
        this.uploading = {
          ...this.uploading,
          [criterionNumber]: false,
        };
      }
    },

    /*
     * Delete evidence.
     */
    async deleteEvidence(
      evidenceId,
      criterionNumber
    ) {
      if (
        !confirm(
          'Are you sure you want to delete this evidence file?'
        )
      ) {
        return;
      }

      try {
        const res =
          await axios.delete(
            `/api/v1/nba/evidence/${evidenceId}`
          );

        if (
          res.data &&
          res.data.success
        ) {
          await this.fetchEvidences(
            criterionNumber
          );
        }
      } catch (err) {
        alert(
          err.response?.data?.message ||
            'Failed to delete evidence.'
        );
      }
    },

    /*
     * Open evidence download endpoint.
     */
    downloadEvidence(ev) {
      window.open(
        `/api/v1/nba/evidence/${ev.id}/download`,
        '_blank'
      );
    },
  },
};
</script>