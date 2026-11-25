<template>
  <div>
    <!-- 0. Smooth Splash Loader during initial session check -->
    <div
      v-if="authChecking"
      class="min-h-screen bg-slate-950 flex flex-col items-center justify-center space-y-4"
    >
      <div
        class="w-10 h-10 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"
      ></div>

      <div class="text-xs text-slate-400 font-mono tracking-wider">
        Validating encrypted session...
      </div>
    </div>

    <!-- 1. If Not Authenticated -> Show Login View -->
    <LoginView
      v-else-if="!currentUser"
      @authenticated="handleAuthenticated"
    />

    <!-- 2. If Authenticated -> Full Dashboard -->
    <div
      v-else
      class="min-h-screen bg-slate-950 text-slate-100 flex font-sans selection:bg-indigo-500 selection:text-white relative overflow-hidden"
    >
      <!-- Ambient Background -->
      <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        <div
          class="absolute -top-32 -left-32 w-[550px] h-[550px] bg-indigo-600/10 rounded-full blur-[120px] animate-float-1"
        ></div>

        <div
          class="absolute top-1/3 -right-24 w-[480px] h-[480px] bg-blue-600/8 rounded-full blur-[140px] animate-float-2"
        ></div>

        <div
          class="absolute -bottom-32 left-1/3 w-[520px] h-[520px] bg-emerald-600/8 rounded-full blur-[130px] animate-float-3"
        ></div>

        <div class="absolute inset-0 bg-ambient-grid opacity-60"></div>
      </div>

      <!-- Sidebar -->
      <aside
        class="no-print w-64 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0 z-40"
      >
        <!-- Sidebar Header -->
        <div
          class="h-16 px-4 flex items-center space-x-3 border-b border-slate-800 bg-slate-900/50"
        >
          <div
            class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-sm overflow-hidden shrink-0"
          >
            <img
              v-if="branding.sidebar_logo"
              :src="branding.sidebar_logo"
              class="w-full h-full object-contain"
              alt="Brand Logo"
            />

            <span v-else>
              {{ branding.app_short_name || 'NBA' }}
            </span>
          </div>

          <div class="truncate">
            <h1
              class="font-bold text-sm tracking-tight text-white flex items-center gap-1.5 truncate"
            >
              {{ branding.app_short_name || 'NBA SAR' }}

              <span
                class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-medium border border-slate-700"
              >
                {{ branding.nba_tier || 'Tier-II' }}
              </span>
            </h1>

            <p class="text-[11px] text-slate-400 truncate">
              {{ currentUser.department || branding.institution_name || 'Institution' }}
            </p>
          </div>
        </div>

        <!-- Sidebar Navigation -->
        <div
          class="flex-1 overflow-y-auto px-3 py-4 space-y-5 scrollbar-thin"
        >
          <!-- Analytics -->
          <div>
            <div
              class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5"
            >
              Analytics & Overview
            </div>

            <nav class="space-y-0.5">
              <button
                @click="activeView = 'overview'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'overview'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="dashboard"
                  size="15"
                  :custom-class="
                    activeView === 'overview'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Executive Dashboard</span>
              </button>

              <button
                @click="activeView = 'about'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'about'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="info"
                  size="15"
                  :custom-class="
                    activeView === 'about'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>About & How It Works</span>
              </button>
            </nav>
          </div>

          <!-- Administration -->
          <div>
            <div
              class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5 flex items-center justify-between"
            >
              <span>Administration</span>

              <span
                v-if="currentUser.role === 'Super Admin'"
                class="text-[9px] px-1 py-0.2 rounded bg-slate-800 text-slate-300 font-mono font-medium border border-slate-700"
              >
                SUPER
              </span>
            </div>

            <nav class="space-y-0.5">
              <button
                @click="activeView = 'staff'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'staff'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="users"
                  size="15"
                  :custom-class="
                    activeView === 'staff'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Staff & Faculty Directory</span>
              </button>

              <button
                @click="activeView = 'academics'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'academics'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="academic"
                  size="15"
                  :custom-class="
                    activeView === 'academics'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Programs & Intake (UGT)</span>
              </button>

              <button
                v-if="currentUser.role === 'Super Admin'"
                @click="activeView = 'settings'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'settings'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="settings"
                  size="15"
                  :custom-class="
                    activeView === 'settings'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>System & Photo Settings</span>
              </button>
            </nav>
          </div>

          <!-- OBE -->
          <div>
            <div
              class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5"
            >
              OBE & Attainment
            </div>

            <nav class="space-y-0.5">
              <button
                @click="activeView = 'courses'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'courses'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="courses"
                  size="15"
                  :custom-class="
                    activeView === 'courses'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Courses & CO Definitions</span>
              </button>

              <button
                @click="activeView = 'blueprint'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'blueprint'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                "
              >
                <AppIcon
                  name="target"
                  size="15"
                  :custom-class="
                    activeView === 'blueprint'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Assessment Blueprint</span>
              </button>

              <button
                @click="activeView = 'marks_upload'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition-all"
                :class="
                  activeView === 'marks_upload'
                    ? 'bg-indigo-600 text-white shadow-lg'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                "
              >
                <AppIcon
                  name="upload"
                  size="15"
                  :custom-class="
                    activeView === 'marks_upload'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Student Marks Upload</span>
              </button>

              <button
                @click="activeView = 'performance'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition"
                :class="
                  activeView === 'performance'
                    ? 'bg-indigo-600 text-white shadow-lg'
                    : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                "
              >
                <AppIcon
                  name="chart"
                  size="15"
                  :custom-class="
                    activeView === 'performance'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Year-on-Year Performance</span>
              </button>

              <button
                @click="activeView = 'matrix'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'matrix'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="matrix"
                  size="15"
                  :custom-class="
                    activeView === 'matrix'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Articulation Matrix (CO-PO)</span>
              </button>

              <button
                @click="activeView = 'attainment'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'attainment'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="target"
                  size="15"
                  :custom-class="
                    activeView === 'attainment'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Attainment & Marks Entry</span>
              </button>

              <button
                @click="activeView = 'surveys'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'surveys'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="users"
                  size="15"
                  :custom-class="
                    activeView === 'surveys'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Stakeholder Surveys</span>
              </button>
            </nav>
          </div>

          <!-- Calculation Engines -->
          <div>
            <div
              class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5"
            >
              Calculation Engines
            </div>

            <nav class="space-y-0.5">
              <button
                @click="activeView = 'sfr_fqi'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'sfr_fqi'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="calculator"
                  size="15"
                  :custom-class="
                    activeView === 'sfr_fqi'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>SFR & FQI Simulator (Crit 5)</span>
              </button>

              <button
                @click="activeView = 'finance'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'finance'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="finance"
                  size="15"
                  :custom-class="
                    activeView === 'finance'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Financial Budgets (Crit 10)</span>
              </button>

              <button
                @click="activeView = 'criteria'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'criteria'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="criteria"
                  size="15"
                  :custom-class="
                    activeView === 'criteria'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Criteria 1 - 10 Master</span>
              </button>
            </nav>
          </div>

          <!-- Compliance -->
          <div>
            <div
              class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1.5"
            >
              Compliance & Output
            </div>

            <nav class="space-y-0.5">
              <button
                @click="activeView = 'workflow'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'workflow'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="workflow"
                  size="15"
                  :custom-class="
                    activeView === 'workflow'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>SAR State Machine & Audit</span>
              </button>

              <!-- Academic Audits -->
              <button
                @click="activeView = 'academic_audits'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'academic_audits'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="clipboard"
                  size="15"
                  :custom-class="
                    activeView === 'academic_audits'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Academic Audits</span>
              </button>

              <button
                @click="activeView = 'document'"
                class="w-full px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2.5 transition text-left"
                :class="
                  activeView === 'document'
                    ? 'bg-indigo-600 text-white shadow-sm font-semibold'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800/60'
                "
              >
                <AppIcon
                  name="printer"
                  size="15"
                  :custom-class="
                    activeView === 'document'
                      ? 'text-white'
                      : 'text-slate-400'
                  "
                />

                <span>Official Printable Report</span>
              </button>
            </nav>
          </div>
        </div>

        <!-- Sidebar User -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/80">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2.5 truncate">
              <div
                class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-slate-200 text-xs shrink-0"
              >
                {{ currentUser.name.charAt(0) }}
              </div>

              <div class="truncate">
                <div class="text-xs font-semibold text-white truncate">
                  {{ currentUser.name }}
                </div>

                <div class="text-[10px] text-slate-400">
                  {{ currentUser.role }}
                </div>
              </div>
            </div>

            <button
              @click="handleLogout"
              title="Sign Out"
              class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 013 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                />
              </svg>
            </button>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <div
        class="flex-1 flex flex-col min-w-0 overflow-hidden print:overflow-visible"
      >
        <!-- Header -->
        <header
          class="no-print h-16 bg-slate-900 border-b border-slate-800 px-6 flex items-center justify-between sticky top-0 z-30"
        >
          <div class="flex items-center space-x-4">
            <div class="text-xs text-slate-400">
              Active Module:
              <strong class="text-slate-200 capitalize font-semibold">
                {{ currentViewTitle }}
              </strong>
            </div>
          </div>

          <div class="flex items-center space-x-3">
            <span
              class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border"
              :class="statusBadgeClass(sarData.current_state)"
            >
              <span
                class="w-1.5 h-1.5 rounded-full mr-2"
                :class="statusDotClass(sarData.current_state)"
              ></span>

              {{ formatState(sarData.current_state) }}
            </span>

            <button
              @click="activeView = 'document'"
              class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-medium border border-slate-700 flex items-center gap-1.5 transition"
            >
              <AppIcon name="printer" size="13" />
              <span>Print SAR</span>
            </button>
          </div>
        </header>

        <!-- Workspace -->
        <main class="flex-1 overflow-y-auto p-6">
          <div
            v-if="loading"
            class="flex flex-col items-center justify-center py-24 space-y-4"
          >
            <div
              class="w-12 h-12 border-4 border-indigo-500/20 border-t-indigo-500 rounded-full animate-spin"
            ></div>

            <div class="text-sm text-slate-400">
              Loading SAR Domain Data & Calculations...
            </div>
          </div>

          <div
            v-else
            class="max-w-7xl mx-auto"
          >
            <!-- 1. Executive Dashboard -->
            <section v-if="activeView === 'overview'">
              <ExecutiveDashboard
                :sar="sarData"
                @navigate="(view) => (activeView = view)"
              />
            </section>

            <!-- 2. About -->
            <section v-if="activeView === 'about'">
              <AboutSystemView
                @navigate="(view) => (activeView = view)"
              />
            </section>

            <!-- 3. Staff -->
            <section v-if="activeView === 'staff'">
              <StaffManagementView />
            </section>

            <!-- 4. Settings -->
            <section v-if="activeView === 'settings'">
              <SettingsView
                @settings-updated="onSettingsUpdated"
              />
            </section>

            <!-- 5. Academics -->
            <section v-if="activeView === 'academics'">
              <AcademicsIntakeView :sar="sarData" />
            </section>

            <!-- 6. Courses -->
            <section v-if="activeView === 'courses'">
              <CoursesManagementView
                :program-id="sarData.program?.id"
                @updated="refreshData"
              />
            </section>

            <!-- 7. Matrix -->
            <section v-if="activeView === 'matrix'">
              <ArticulationMatrixEditor
                :program-id="sarData.program?.id"
                @updated="refreshData"
              />
            </section>

            <!-- 7A. Assessment Blueprint -->
            <section v-if="activeView === 'blueprint'">
              <AssessmentBlueprintView
                :program-id="sarData.program?.id"
                @updated="refreshData"
              />
            </section>

            <!-- 7B. Year-on-Year Performance -->
            <section v-if="activeView === 'performance'">
              <CoursePerformanceView
                :program-id="sarData.program?.id"
              />
            </section>

            <!-- 7C. Student Marks Upload -->
            <section v-if="activeView === 'marks_upload'">
              <StudentMarksUploadView
                :program-id="sarData.program?.id"
                @updated="refreshData"
              />
            </section>

            <!-- 8. Attainment -->
            <section v-if="activeView === 'attainment'">
              <AttainmentDashboard
                :attainment="sarData.live_engines?.attainment"
                :program-id="sarData.program?.id"
                @updated="refreshData"
              />
            </section>

            <!-- 9. Surveys -->
            <section v-if="activeView === 'surveys'">
              <StakeholderSurveyView />
            </section>

            <!-- 10. SFR & FQI -->
            <section v-if="activeView === 'sfr_fqi'">
              <SfrSimulator
                :sfr="sarData.live_engines?.sfr"
                :program="sarData.program"
              />

              <div class="my-8 border-t border-slate-800"></div>

              <FqiSimulator
                :fqi="sarData.live_engines?.fqi"
                :cadre="sarData.live_engines?.cadre"
                :program="sarData.program"
              />
            </section>

            <!-- 11. Finance -->
            <section v-if="activeView === 'finance'">
              <FinanceGovernanceView :sar="sarData" />
            </section>

            <!-- 12. Criteria 1-10 -->
            <section v-if="activeView === 'criteria'">
              <CriteriaExplorer
                :criteria="sarData.criteria || []"
                :sar="sarData"
                :report-id="sarData.report_id"
              />
            </section>

            <!-- 13. Workflow -->
            <section v-if="activeView === 'workflow'">
              <SarWorkflowPanel
                :report-id="sarData.report_id"
                @state-changed="refreshData"
              />
            </section>

            <!-- 14. Academic Audits -->
            <section v-if="activeView === 'academic_audits'">
              <AcademicAuditView />
            </section>

            <!-- 15. Printable Report -->
            <section v-if="activeView === 'document'">
              <PrintableSarReport :sar="sarData" />
            </section>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

import AppIcon from './AppIcon.vue';
import AssessmentBlueprintView from './AssessmentBlueprintView.vue';
import LoginView from './LoginView.vue';
import AboutSystemView from './AboutSystemView.vue';
import StaffManagementView from './StaffManagementView.vue';
import SettingsView from './SettingsView.vue';
import CoursesManagementView from './CoursesManagementView.vue';
import ExecutiveDashboard from './ExecutiveDashboard.vue';
import AcademicsIntakeView from './AcademicsIntakeView.vue';
import SfrSimulator from './SfrSimulator.vue';
import FqiSimulator from './FqiSimulator.vue';
import ArticulationMatrixEditor from './ArticulationMatrixEditor.vue';
import AttainmentDashboard from './AttainmentDashboard.vue';
import StakeholderSurveyView from './StakeholderSurveyView.vue';
import FinanceGovernanceView from './FinanceGovernanceView.vue';
import CriteriaExplorer from './CriteriaExplorer.vue';
import SarWorkflowPanel from './SarWorkflowPanel.vue';
import PrintableSarReport from './PrintableSarReport.vue';
import StudentMarksUploadView from './StudentMarksUploadView.vue';
import CoursePerformanceView from './CoursePerformanceView.vue';
import AcademicAuditView from './AcademicAuditView.vue';

export default {
  name: 'MainApp',

  components: {
    AppIcon,
    StudentMarksUploadView,
    LoginView,
    AssessmentBlueprintView,
    AboutSystemView,
    StaffManagementView,
    SettingsView,
    CoursesManagementView,
    ExecutiveDashboard,
    AcademicsIntakeView,
    SfrSimulator,
    FqiSimulator,
    ArticulationMatrixEditor,
    AttainmentDashboard,
    StakeholderSurveyView,
    FinanceGovernanceView,
    CriteriaExplorer,
    SarWorkflowPanel,
    PrintableSarReport,
    CoursePerformanceView,
    AcademicAuditView,
  },

  data() {
    return {
      currentUser: null,
      authChecking: true,
      loading: true,
      activeView: 'overview',
      sarData: {},

      branding: {
        app_name: 'NBA SAR & OBE Platform',
        app_short_name: 'NBA',
        nba_tier: 'Tier-II',
        institution_name:
          'National Institute of Engineering & Technology',
        sidebar_logo: '',
        favicon: '',
      },
    };
  },

  computed: {
    currentViewTitle() {
      const titles = {
        overview: 'Executive Dashboard & KPI Scores',
        about: 'About System & Operational Workflow Guide',
        staff: 'Staff & Faculty Directory Management',
        settings: 'System Branding & Photo Settings',
        academics: 'Programs & Student Intake Cohorts',
        courses: 'Course Catalog & Outcome Definitions (COs)',
        matrix: 'Curriculum Articulation Matrix (CO-PO)',
        blueprint: 'Assessment Blueprint',
        marks_upload: 'Student Marks Upload',
        attainment: 'Direct & Indirect Attainment Pipeline',
        surveys: 'Stakeholder Survey Management',
        sfr_fqi: 'Faculty Indices (SFR & FQI Calculation)',
        finance: 'Financial Resources & Budget Utilization',
        criteria: 'Criteria 1 - 10 Self-Assessment Breakdown',
        workflow: 'SAR Approval State Machine & Audit Trail',
        academic_audits: 'Academic Audits',
        document: 'Official NBA Tier-II SAR Report Document',
        performance: 'Year-on-Year Course Performance',
      };

      return titles[this.activeView] || this.activeView;
    },
  },

  mounted() {
    this.fetchBranding();
    this.checkAuth();
  },

  methods: {
    async fetchBranding() {
      try {
        const res = await axios.get('/api/v1/settings');

        if (res.data && res.data.success) {
          this.branding = {
            ...this.branding,
            ...res.data.data,
          };

          if (this.branding.favicon) {
            let link = document.querySelector(
              "link[rel~='icon']"
            );

            if (link) {
              link.href = this.branding.favicon;
            }
          }
        }
      } catch (err) {
        // Branding failure should not block dashboard.
      }
    },

    onSettingsUpdated(updatedSettings) {
      this.branding = {
        ...this.branding,
        ...updatedSettings,
      };

      this.fetchDashboardData();
    },

    async checkAuth() {
      try {
        const res = await axios.get('/api/v1/auth/me');

        if (
          res.data &&
          res.data.success &&
          res.data.authenticated
        ) {
          this.currentUser = res.data.user;

          await this.fetchDashboardData();
        } else {
          this.currentUser = null;
          this.loading = false;
        }
      } catch (err) {
        this.currentUser = null;
        this.loading = false;
      } finally {
        this.authChecking = false;
      }
    },

    handleAuthenticated(user) {
      this.currentUser = user;
      this.fetchDashboardData();
    },

    async handleLogout() {
      try {
        await axios.post('/api/v1/auth/logout');
      } catch (err) {
        console.error('Logout error:', err);
      } finally {
        this.currentUser = null;
      }
    },

    async fetchDashboardData() {
      this.loading = true;

      try {
        const response = await axios.get(
          '/api/v1/nba/dashboard'
        );

        if (
          response.data &&
          response.data.success
        ) {
          this.sarData = response.data.data;
        }
      } catch (error) {
        console.error(
          'Failed to load SAR dashboard data:',
          error
        );
      } finally {
        this.loading = false;
      }
    },

    refreshData() {
      this.fetchDashboardData();
    },

    formatState(state) {
      if (!state) return 'Draft';

      return state.replace(/_/g, ' ');
    },

    statusBadgeClass(state) {
      switch (state) {
        case 'APPROVED_LOCKED':
          return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';

        case 'INSTITUTIONAL_REVIEW':
          return 'bg-purple-500/10 text-purple-400 border-purple-500/30';

        case 'DEPARTMENT_CONSOLIDATION':
          return 'bg-amber-500/10 text-amber-400 border-amber-500/30';

        default:
          return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
      }
    },

    statusDotClass(state) {
      switch (state) {
        case 'APPROVED_LOCKED':
          return 'bg-emerald-400 animate-pulse';

        case 'INSTITUTIONAL_REVIEW':
          return 'bg-purple-400';

        case 'DEPARTMENT_CONSOLIDATION':
          return 'bg-amber-400';

        default:
          return 'bg-blue-400';
      }
    },
  },
};
</script>