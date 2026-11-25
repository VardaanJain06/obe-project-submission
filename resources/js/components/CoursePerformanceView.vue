<template>
    <div class="space-y-6">

        <!-- Header -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-indigo-400 font-semibold">
                        OBE Performance Analytics
                    </p>
                    <h1 class="mt-2 text-2xl font-bold text-white">
                        Course Performance — Year on Year
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Compare course outcome attainment across academic years.
                    </p>
                </div>

                <button
                    @click="loadPerformance"
                    :disabled="loading"
                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500
                           disabled:opacity-50 text-white text-sm font-semibold"
                >
                    {{ loading ? 'Loading...' : 'Refresh Data' }}
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Select Course
                    </label>

                    <select
                        v-model="selectedCourseId"
                        @change="loadPerformance"
                        class="w-full rounded-xl border border-slate-700 bg-slate-950
                               px-4 py-3 text-sm text-white outline-none
                               focus:border-indigo-500"
                    >
                        <option value="" disabled>
                            Select a course
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

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Academic Years
                    </label>

                    <div class="h-[46px] flex items-center px-4 rounded-xl
                                border border-slate-800 bg-slate-950 text-sm text-slate-300">
                        {{ performance.length }} year{{ performance.length === 1 ? '' : 's' }} available
                    </div>
                </div>

            </div>
        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="rounded-2xl border border-slate-800 bg-slate-900/70 p-12 text-center"
        >
            <div class="text-indigo-400 text-sm font-medium">
                Loading course performance...
            </div>
        </div>

        <!-- Error -->
        <div
            v-else-if="errorMessage"
            class="rounded-2xl border border-red-900/50 bg-red-950/20 p-5 text-red-300 text-sm"
        >
            {{ errorMessage }}
        </div>

        <!-- Empty -->
        <div
            v-else-if="!performance.length"
            class="rounded-2xl border border-slate-800 bg-slate-900/70 p-12 text-center"
        >
            <div class="text-slate-400 text-sm">
                No attainment data available for this course yet.
            </div>
        </div>

        <!-- KPI Cards -->
        <div
            v-else
            class="grid grid-cols-1 md:grid-cols-3 gap-4"
        >
            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500">
                    Latest Attainment
                </p>
                <div class="mt-2 text-3xl font-bold text-white">
                    {{ formatScore(latestScore) }}
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    {{ latestYear }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500">
                    Previous Attainment
                </p>
                <div class="mt-2 text-3xl font-bold text-white">
                    {{ formatScore(previousScore) }}
                </div>
                <p class="mt-1 text-xs text-slate-500">
                    {{ previousYear || '—' }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5">
                <p class="text-xs uppercase tracking-wider text-slate-500">
                    Year-on-Year Change
                </p>

                <div
                    class="mt-2 text-3xl font-bold"
                    :class="change >= 0 ? 'text-emerald-400' : 'text-rose-400'"
                >
                    {{ change >= 0 ? '+' : '' }}{{ change.toFixed(2) }}
                </div>

                <p class="mt-1 text-xs text-slate-500">
                    attainment level difference
                </p>
            </div>
        </div>

        <!-- Comparison Table -->
        <div
            v-if="performance.length"
            class="rounded-2xl border border-slate-800 bg-slate-900/70 overflow-hidden"
        >
            <div class="px-6 py-5 border-b border-slate-800">
                <h2 class="text-lg font-semibold text-white">
                    Academic Year Comparison
                </h2>
                <p class="text-sm text-slate-400 mt-1">
                    Direct course outcome attainment by academic year.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-950/70">
                        <tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Academic Year</th>
                            <th class="px-6 py-4">Attainment</th>
                            <th class="px-6 py-4">Previous Year</th>
                            <th class="px-6 py-4">Change</th>
                            <th class="px-6 py-4">Trend</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-800">
                        <tr
                            v-for="(row, index) in performance"
                            :key="row.year_id || index"
                            class="hover:bg-slate-800/30"
                        >
                            <td class="px-6 py-4 font-medium text-white">
                                {{ row.year }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-semibold text-indigo-300">
                                    {{ formatScore(row.score) }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-slate-400">
                                {{
                                    index > 0
                                        ? formatScore(performance[index - 1].score)
                                        : '—'
                                }}
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    v-if="index > 0"
                                    :class="row.score >= performance[index - 1].score
                                        ? 'text-emerald-400'
                                        : 'text-rose-400'"
                                >
                                    {{
                                        row.score >= performance[index - 1].score
                                            ? '+'
                                            : ''
                                    }}{{
                                        (
                                            row.score -
                                            performance[index - 1].score
                                        ).toFixed(2)
                                    }}
                                </span>

                                <span v-else class="text-slate-600">
                                    —
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    v-if="index > 0"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                    :class="row.score >= performance[index - 1].score
                                        ? 'bg-emerald-500/10 text-emerald-400'
                                        : 'bg-rose-500/10 text-rose-400'"
                                >
                                    {{ row.score >= performance[index - 1].score
                                        ? 'Improved'
                                        : 'Declined' }}
                                </span>

                                <span v-else class="text-slate-600">
                                    Baseline
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'CoursePerformanceView',

    props: {
        programId: {
            type: [Number, String],
            required: true
        }
    },

    data() {
        return {
            courses: [],
            selectedCourseId: '',
            performance: [],
            loading: false,
            errorMessage: ''
        };
    },

    computed: {
        latestScore() {
            return this.performance.length
                ? this.performance[this.performance.length - 1].score
                : 0;
        },

        previousScore() {
            return this.performance.length > 1
                ? this.performance[this.performance.length - 2].score
                : 0;
        },

        latestYear() {
            return this.performance.length
                ? this.performance[this.performance.length - 1].year
                : '—';
        },

        previousYear() {
            return this.performance.length > 1
                ? this.performance[this.performance.length - 2].year
                : null;
        },

        change() {
            return this.latestScore - this.previousScore;
        }
    },

    async mounted() {
        await this.loadCourses();
    },

    methods: {
        async loadCourses() {
            try {
                const response = await axios.get('/api/v1/courses', {
                    params: {
                        program_id: this.programId
                    }
                });

                this.courses = response.data.data?.courses || [];

                if (this.courses.length) {
                    this.selectedCourseId = this.courses[0].id;
                    await this.loadPerformance();
                }
            } catch (error) {
                console.error(error);
                this.errorMessage = 'Unable to load courses.';
            }
        },

        async loadPerformance() {
            if (!this.selectedCourseId) return;

            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await axios.get(
                    `/api/v1/nba/attainment/course/${this.selectedCourseId}/performance`
                );

                const data = response.data?.data || response.data;

                this.performance = this.normalizePerformance(data);
            } catch (error) {
                console.error(error);

                this.performance = [];

                this.errorMessage =
                    'Performance data could not be loaded for this course.';
            } finally {
                this.loading = false;
            }
        },

        normalizePerformance(data) {
            /*
             * Handles the common API shapes used by the attainment endpoint.
             */

            let rows = [];

            if (Array.isArray(data)) {
                rows = data;
            } else if (Array.isArray(data?.years)) {
                rows = data.years;
            } else if (Array.isArray(data?.performance)) {
                rows = data.performance;
            } else if (Array.isArray(data?.attainment)) {
                rows = data.attainment;
            } else if (Array.isArray(data?.results)) {
                rows = data.results;
            }

            return rows
                .map((row) => {
                    const score =
                        Number(
                            row.final_score ??
                            row.direct_attainment ??
                            row.attainment ??
                            row.score ??
                            row.value ??
                            0
                        );

                    const year =
                        row.academic_year?.name ??
                        row.academic_year?.label ??
                        row.academic_year?.year ??
                        row.year ??
                        row.academic_year_id ??
                        'Year';

                    return {
                        year_id: row.academic_year_id ?? row.year_id ?? null,
                        year: String(year),
                        score: Number.isFinite(score) ? score : 0
                    };
                })
                .sort((a, b) =>
                    String(a.year).localeCompare(String(b.year), undefined, {
                        numeric: true
                    })
                );
        },

        formatScore(score) {
            return Number(score || 0).toFixed(2);
        }
    }
};
</script>