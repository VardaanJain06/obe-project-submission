<template>
  <div class="space-y-6">
    <!-- Hero Scorecard & Program Overview Banner -->
    <div class="rounded-xl p-6 bg-slate-900 border border-slate-800 shadow-sm">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">

        <!-- Program Details -->
        <div class="lg:col-span-7 space-y-3">

          <div
            class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700 text-xs font-medium text-slate-300"
          >
            <AppIcon name="academic" size="13" />

            <span>
              {{ sar.program?.institution || 'Institution' }}
            </span>

            <span class="text-slate-500">•</span>

            <span>
              {{ sar.program?.department || 'Department' }}
            </span>
          </div>

          <h2
            class="text-2xl sm:text-3xl font-bold tracking-tight text-white"
          >
            {{ sar.program?.name || 'Program Title' }}
          </h2>

          <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
            Tier-II Engineering Accreditation Evaluation •
            Sanctioned Intake:

            <span class="text-slate-200 font-medium">
              {{ sar.program?.sanctioned_intake || 120 }}
            </span>

            • Level:

            <span class="text-slate-200 font-medium">
              Undergraduate (4 Years)
            </span>
          </p>

          <!-- NBA Readiness Tier -->
          <div class="pt-2 flex flex-wrap items-center gap-3">

            <div
              class="px-3 py-1 rounded-md text-xs font-semibold flex items-center gap-2 border"
              :class="readinessVerdict.badgeClass"
            >
              <AppIcon name="target" size="13" />

              <span>
                NBA Readiness: {{ readinessVerdict.status }}
              </span>
            </div>

            <span class="text-xs text-slate-400">
              Readiness Score:

              <strong class="text-slate-300">
                {{ readinessVerdict.score }} / 1000
              </strong>
            </span>
          </div>

          <!-- Readiness Scale -->
          <div class="flex flex-wrap gap-2 pt-1">
            <span class="text-[10px] text-slate-500">
              Scale:
            </span>

            <span class="text-[10px] text-slate-400">
              Platinum ≥ 900
            </span>

            <span class="text-slate-700">•</span>

            <span class="text-[10px] text-slate-400">
              Gold 750–899
            </span>

            <span class="text-slate-700">•</span>

            <span class="text-[10px] text-slate-400">
              Silver 600–749
            </span>

            <span class="text-slate-700">•</span>

            <span class="text-[10px] text-slate-400">
              At Risk &lt; 600
            </span>
          </div>
        </div>

        <!-- Overall Score Dial -->
        <div
          class="lg:col-span-5 flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center justify-center lg:justify-end gap-6 bg-slate-950/70 p-5 rounded-xl border border-slate-800/80"
        >

          <!-- Total Score -->
          <div class="text-center">

            <div
              class="text-xs uppercase tracking-wider font-semibold text-slate-400"
            >
              Total Claimed Score
            </div>

            <div
              class="text-4xl sm:text-5xl font-bold text-white mt-1"
            >
              {{ sar.total_claimed_marks || 0 }}
            </div>

            <div class="text-xs text-slate-400 mt-0.5">
              out of

              <span class="text-slate-300 font-medium">
                1000.0 Marks
              </span>
            </div>
          </div>

          <div
            class="h-12 w-px bg-slate-800 hidden sm:block"
          ></div>

          <!-- Overall Compliance -->
          <div class="text-center">

            <div
              class="text-xs uppercase tracking-wider font-semibold text-slate-400"
            >
              Overall Compliance
            </div>

            <div
              class="text-3xl sm:text-4xl font-bold mt-1"
              :class="readinessVerdict.scoreClass"
            >
              {{ sar.total_percentage || 0 }}%
            </div>

            <div
              class="w-28 bg-slate-800 h-2 rounded-full overflow-hidden mt-2 mx-auto"
            >
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="readinessVerdict.progressClass"
                :style="{
                  width: Math.min(
                    Math.max(Number(sar.total_percentage) || 0,
                    0),
                    100
                  ) + '%'
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Metric Pillars -->
    <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5"
    >

      <!-- Pillar 1: SFR -->
      <div
        @click="$emit('navigate', 'sfr_fqi')"
        class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer"
      >
        <div
          class="flex items-center justify-between text-slate-400 text-xs"
        >
          <span>Student-Faculty Ratio</span>

          <span
            class="text-slate-400 font-mono text-[11px] bg-slate-800/80 px-1.5 py-0.5 rounded"
          >
            Crit 5.1
          </span>
        </div>

        <div class="mt-2 text-2xl font-bold text-white">
          {{ sar.live_engines?.sfr?.avg_sfr || 0 }} : 1
        </div>

        <div
          class="mt-1 flex items-center justify-between text-[11px]"
        >
          <span class="text-emerald-400 font-medium">
            Score:
            {{ sar.live_engines?.sfr?.claimed_marks || 0 }}/20
          </span>

          <span class="text-slate-400">
            Target ≤ 15:1
          </span>
        </div>
      </div>

      <!-- Pillar 2: FQI -->
      <div
        @click="$emit('navigate', 'sfr_fqi')"
        class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer"
      >
        <div
          class="flex items-center justify-between text-slate-400 text-xs"
        >
          <span>Faculty Qualification</span>

          <span
            class="text-slate-400 font-mono text-[11px] bg-slate-800/80 px-1.5 py-0.5 rounded"
          >
            Crit 5.3
          </span>
        </div>

        <div class="mt-2 text-2xl font-bold text-white">
          {{ sar.live_engines?.fqi?.avg_fqi || 0 }}

          <span class="text-sm font-normal text-slate-400">
            / 25.0
          </span>
        </div>

        <div
          class="mt-1 flex items-center justify-between text-[11px]"
        >
          <span class="text-slate-300">
            Ph.D. + M.Tech Indexed
          </span>

          <span class="text-slate-400">
            Max 25
          </span>
        </div>
      </div>

      <!-- Pillar 3: PO Attainment -->
      <div
        @click="$emit('navigate', 'attainment')"
        class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer"
      >
        <div
          class="flex items-center justify-between text-slate-400 text-xs"
        >
          <span>CO-PO Attainment Avg</span>

          <span
            class="text-slate-400 font-mono text-[11px] bg-slate-800/80 px-1.5 py-0.5 rounded"
          >
            Crit 3.3
          </span>
        </div>

        <div class="mt-2 text-2xl font-bold text-white">
          {{
            sar.live_engines?.attainment?.overall_po_average || 2.62
          }}

          <span class="text-sm font-normal text-slate-400">
            / 3.0
          </span>
        </div>

        <div
          class="mt-1 flex items-center justify-between text-[11px]"
        >
          <span class="text-slate-300">
            80% Direct + 20% Indirect
          </span>

          <span class="text-slate-400">
            Max 120 Pts
          </span>
        </div>
      </div>

      <!-- Pillar 4: Placement Index -->
      <div
        @click="$emit('navigate', 'criteria')"
        class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer"
      >
        <div
          class="flex items-center justify-between text-slate-400 text-xs"
        >
          <span>Placement & Higher Studies</span>

          <span
            class="text-slate-400 font-mono text-[11px] bg-slate-800/80 px-1.5 py-0.5 rounded"
          >
            Crit 4.5
          </span>
        </div>

        <div class="mt-2 text-2xl font-bold text-white">
          {{
            placementPercentage
          }}%
        </div>

        <div
          class="mt-1 flex items-center justify-between text-[11px]"
        >
          <span class="text-emerald-400 font-medium">
            Score:
            {{ sar.live_engines?.placement?.claimed_marks || 36 }}/40
          </span>

          <span class="text-slate-400">
            Median 7.8 LPA
          </span>
        </div>
      </div>

      <!-- Pillar 5: Budget Utilization -->
      <div
        @click="$emit('navigate', 'finance')"
        class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition cursor-pointer"
      >
        <div
          class="flex items-center justify-between text-slate-400 text-xs"
        >
          <span>Budget Utilization</span>

          <span
            class="text-slate-400 font-mono text-[11px] bg-slate-800/80 px-1.5 py-0.5 rounded"
          >
            Crit 10.1
          </span>
        </div>

        <div class="mt-2 text-2xl font-bold text-white">
          {{
            sar.live_engines?.budget?.overall_utilization || 94.3
          }}%
        </div>

        <div
          class="mt-1 flex items-center justify-between text-[11px]"
        >
          <span class="text-emerald-400 font-medium">
            Score:
            {{ sar.live_engines?.budget?.claimed_marks || 28 }}/30
          </span>

          <span class="text-slate-400">
            3-Yr Average
          </span>
        </div>
      </div>
    </div>

    <!-- Criteria 1 to 10 Interactive Progress Grid -->
    <div class="space-y-3">

      <div class="flex items-center justify-between">
        <h3
          class="text-base font-bold text-white flex items-center gap-2"
        >
          <AppIcon
            name="criteria"
            size="16"
            custom-class="text-slate-400"
          />

          <span>
            Criteria 1 to 10 Performance Breakdown
          </span>
        </h3>

        <button
          @click="$emit('navigate', 'criteria')"
          class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
        >
          View Detailed Evidence & Summaries →
        </button>
      </div>

      <div
        class="grid grid-cols-1 md:grid-cols-2 gap-3"
      >
        <div
          v-for="c in sar.criteria"
          :key="c.number"
          class="p-4 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between"
        >

          <div
            class="flex items-start justify-between gap-2"
          >
            <div>

              <div
                class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider"
              >
                Criterion {{ c.number }}
              </div>

              <div
                class="text-sm font-semibold text-white mt-0.5"
              >
                {{ c.title }}
              </div>
            </div>

            <div class="text-right shrink-0">

              <div
                class="text-sm font-bold text-slate-200"
              >
                {{ c.claimed_marks }}

                <span
                  class="text-xs text-slate-400 font-normal"
                >
                  / {{ c.max_marks }}
                </span>
              </div>

              <div
                class="text-[10px] text-slate-400 font-mono"
              >
                {{ c.percentage }}%
              </div>
            </div>
          </div>

          <p
            class="text-xs text-slate-400 mt-2 line-clamp-1"
          >
            {{ c.summary }}
          </p>

          <!-- Progress Bar -->
          <div
            class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden mt-3"
          >
            <div
              class="h-full rounded-full transition-all duration-300"
              :class="
                c.percentage >= 80
                  ? 'bg-emerald-500'
                  : 'bg-indigo-500'
              "
              :style="{
                width:
                  Math.min(
                    Math.max(Number(c.percentage) || 0, 0),
                    100
                  ) + '%'
              }"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AppIcon from './AppIcon.vue';
import axios from 'axios';

export default {
  name: 'ExecutiveDashboard',

  components: {
    AppIcon,
  },

  props: {
    sar: {
      type: Object,
      required: true,
    },
  },

  data() {
    return {
      liveReadiness: null,
    };
  },

  mounted() {
    this.fetchReadiness();
  },

  watch: {
    'sar.report_id': function (newVal) {
      if (newVal) {
        this.fetchReadiness();
      }
    },
  },

  methods: {
    async fetchReadiness() {
      if (!this.sar.report_id) return;
      try {
        const params = { sar_report_id: this.sar.report_id };
        const res = await axios.get('/api/v1/sar/nba-readiness', { params });
        if (res.data && res.data.success) {
          this.liveReadiness = res.data.data;
        }
      } catch (err) {
        console.error('Failed to fetch NBA readiness:', err);
      }
    }
  },

  computed: {
    /*
     * Placement percentage
     *
     * Backend value is expected to be a decimal such as:
     * 0.84 -> 84%
     *
     * If backend value is missing, existing fallback of 84%
     * is preserved.
     */
    placementPercentage() {
      const value =
        this.sar.live_engines?.placement?.avg_placement_index;

      if (value === null || value === undefined || value === '') {
        return 84;
      }

      const percentage = Number(value) * 100;

      if (Number.isNaN(percentage)) {
        return 84;
      }

      return Math.round(percentage);
    },

    /*
     * NBA Readiness / Tier
     *
     * Backend-first approach:
     *
     * 1. If backend sends readiness tier, use it.
     * 2. Otherwise calculate the tier from total score.
     *
     * Expected tiers:
     *
     * 900+       = Platinum
     * 750-899    = Gold
     * 600-749    = Silver
     * <600       = At Risk
     */
    readinessVerdict() {
      const score = Number(this.sar.total_claimed_marks) || 0;

      /*
       * Support multiple possible backend property names
       * so the dashboard can work with NbaReadinessService
       * without breaking if the response naming differs.
       */
      const backendReadiness =
        this.liveReadiness ||
        this.sar.readiness ||
        this.sar.nba_readiness ||
        this.sar.readiness_tier ||
        null;

      let tier = null;

      /*
       * Backend may return:
       *
       * "Platinum"
       * "Gold"
       * "Silver"
       * "At Risk"
       *
       * or an object such as:
       *
       * {
       *   tier: "Gold",
       *   score: 820
       * }
       */
      if (typeof backendReadiness === 'string') {
        tier = backendReadiness;
      } else if (
        backendReadiness &&
        typeof backendReadiness === 'object'
      ) {
        tier =
          backendReadiness.tier ||
          backendReadiness.status ||
          backendReadiness.label ||
          null;
      }

      /*
       * Normalize backend tier.
       */
      if (tier) {
        const normalizedTier = String(tier)
          .trim()
          .toLowerCase();

        if (normalizedTier === 'platinum') {
          tier = 'Platinum';
        } else if (normalizedTier === 'gold') {
          tier = 'Gold';
        } else if (normalizedTier === 'silver') {
          tier = 'Silver';
        } else if (
          normalizedTier === 'at risk' ||
          normalizedTier === 'at_risk' ||
          normalizedTier === 'atrisk'
        ) {
          tier = 'At Risk';
        } else {
          tier = null;
        }
      }

      /*
       * Fallback calculation.
       *
       * This keeps the UI functional even before the backend
       * NbaReadinessService is connected.
       */
      if (!tier) {
        if (score >= 900) {
          tier = 'Platinum';
        } else if (score >= 750) {
          tier = 'Gold';
        } else if (score >= 600) {
          tier = 'Silver';
        } else {
          tier = 'At Risk';
        }
      }

      /*
       * Return presentation data.
       */
      switch (tier) {
        case 'Platinum':
          return {
            status: 'Platinum',
            score,
            badgeClass:
              'bg-amber-400/15 text-amber-300 border-amber-400/30',
            scoreClass:
              'text-amber-300',
            progressClass:
              'bg-amber-400',
          };

        case 'Gold':
          return {
            status: 'Gold',
            score,
            badgeClass:
              'bg-yellow-500/15 text-yellow-300 border-yellow-500/30',
            scoreClass:
              'text-yellow-300',
            progressClass:
              'bg-yellow-500',
          };

        case 'Silver':
          return {
            status: 'Silver',
            score,
            badgeClass:
              'bg-slate-400/15 text-slate-200 border-slate-400/30',
            scoreClass:
              'text-slate-200',
            progressClass:
              'bg-slate-400',
          };

        default:
          return {
            status: 'At Risk',
            score,
            badgeClass:
              'bg-rose-500/15 text-rose-300 border-rose-500/30',
            scoreClass:
              'text-rose-400',
            progressClass:
              'bg-rose-500',
          };
      }
    },
  },
};
</script>