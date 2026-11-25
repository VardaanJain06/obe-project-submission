<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-xs font-semibold border border-indigo-500/20">
          <AppIcon name="settings" size="12" />
          <span>Super Admin Configuration</span>
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          System Settings, Branding & Data Management
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Configure login screen logos, sidebar brand icons, favicon, official report headers, and institutional accreditation details.
        </p>
      </div>

      <div>
        <button @click="saveSettings" :disabled="saving"
                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition disabled:opacity-50">
          <span v-if="saving" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
          <span>{{ saving ? 'Saving Changes...' : 'Save All Settings' }}</span>
        </button>
      </div>
    </div>

    <!-- Success Message Toast -->
    <div v-if="successMsg" class="p-3.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-lg">
      <div class="flex items-center gap-2">
        <span>✅</span>
        <span class="font-semibold">{{ successMsg }}</span>
      </div>
      <button @click="successMsg = ''" class="text-emerald-400 hover:text-white">✕</button>
    </div>

    <!-- Live Preview Banner -->
    <div class="glass-card rounded-2xl p-5 border border-indigo-500/30 bg-slate-900/70 flex flex-col md:flex-row items-center justify-between gap-6">
      <div class="flex items-center space-x-4">
        <!-- Live Logo / Icon Preview -->
        <div class="w-14 h-14 rounded-2xl bg-slate-950 border border-slate-700 flex items-center justify-center p-1 overflow-hidden shrink-0 shadow-lg">
          <img v-if="form.sidebar_logo" :src="form.sidebar_logo" class="w-full h-full object-contain rounded-xl" alt="App Logo" />
          <div v-else class="w-full h-full rounded-xl bg-gradient-to-tr from-indigo-600 to-emerald-400 flex items-center justify-center font-black text-white text-lg">
            {{ form.app_short_name || 'NBA' }}
          </div>
        </div>

        <div>
          <div class="flex items-center gap-2">
            <h4 class="text-base font-bold text-white">{{ form.app_name || 'NBA SAR Platform' }}</h4>
            <span class="text-[10px] px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-semibold border border-indigo-500/30">
              {{ form.nba_tier || 'Tier-II' }}
            </span>
          </div>
          <p class="text-xs text-slate-400 mt-0.5">{{ form.app_tagline || 'Outcome-Based Education Platform' }}</p>
          <div class="text-[11px] text-slate-500 mt-1">
            {{ form.institution_name }} ({{ form.institution_code }}) • Active Cycle: <span class="text-emerald-400 font-mono">{{ form.current_academic_year }}</span>
          </div>
        </div>
      </div>

      <div class="text-right text-xs text-slate-400 hidden lg:block">
        <span class="inline-block px-3 py-1 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
          Live Branding Active
        </span>
      </div>
    </div>

    <!-- Setting Tabs Navigation -->
    <div class="flex space-x-2 border-b border-slate-800 pb-2">
      <button v-for="tab in settingTabs" :key="tab.id"
              @click="activeSettingTab = tab.id"
              class="px-4 py-2 text-xs font-semibold rounded-lg transition flex items-center gap-2"
              :class="activeSettingTab === tab.id ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/40'">
        <AppIcon :name="tab.icon" size="14" />
        <span>{{ tab.label }}</span>
      </button>
    </div>

    <!-- Tab 1: 🎨 Branding & Logos -->
    <div v-if="activeSettingTab === 'branding'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- 1. Login Page Logo & Icon -->
      <div class="glass-card rounded-xl p-5 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div>
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <span>Login Page Logo & Icon</span>
            </h4>
            <p class="text-[11px] text-slate-400">Displayed prominently on the portal sign-in card</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 p-1 flex items-center justify-center shrink-0">
            <img v-if="form.login_logo" :src="form.login_logo" class="w-full h-full object-contain rounded-lg" />
            <span v-else class="text-xs font-bold text-indigo-400">Logo</span>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Logo Image URL / Path</label>
            <input type="text" v-model="form.login_logo" placeholder="https://example.com/logo.png"
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div>
            <label class="font-semibold text-slate-300 block mb-1">Upload Logo Image File</label>
            <input type="file" @change="uploadImage($event, 'login_logo')" accept="image/*"
                   class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer cursor-pointer" />
          </div>
        </div>
      </div>

      <!-- 2. Sidebar Top Logo / Brand Icon -->
      <div class="glass-card rounded-xl p-5 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div>
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <span>Sidebar Brand Icon</span>
            </h4>
            <p class="text-[11px] text-slate-400">Header icon in the left navigation sidebar</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 p-1 flex items-center justify-center shrink-0">
            <img v-if="form.sidebar_logo" :src="form.sidebar_logo" class="w-full h-full object-contain rounded-lg" />
            <span v-else class="text-xs font-bold text-emerald-400">Icon</span>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Sidebar Icon URL</label>
            <input type="text" v-model="form.sidebar_logo" placeholder="https://example.com/icon.png"
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div>
            <label class="font-semibold text-slate-300 block mb-1">Upload Sidebar Icon</label>
            <input type="file" @change="uploadImage($event, 'sidebar_logo')" accept="image/*"
                   class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer cursor-pointer" />
          </div>
        </div>
      </div>

      <!-- 3. Browser Tab Favicon -->
      <div class="glass-card rounded-xl p-5 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div>
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <span>Browser Favicon Icon</span>
            </h4>
            <p class="text-[11px] text-slate-400">Displayed in browser tabs and bookmarks</p>
          </div>
          <div class="w-8 h-8 rounded-lg bg-slate-950 border border-slate-800 p-1 flex items-center justify-center shrink-0">
            <img v-if="form.favicon" :src="form.favicon" class="w-full h-full object-contain" />
            <span v-else class="text-[10px] text-indigo-300">Fav</span>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Favicon URL / Path</label>
            <input type="text" v-model="form.favicon" placeholder="/favicon.ico or https://..."
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div>
            <label class="font-semibold text-slate-300 block mb-1">Upload Favicon (.ico, .png, .svg)</label>
            <input type="file" @change="uploadImage($event, 'favicon')" accept=".ico,.png,.svg,.webp"
                   class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer cursor-pointer" />
          </div>
        </div>
      </div>

      <!-- 4. Official Report / PDF Header Logo -->
      <div class="glass-card rounded-xl p-5 border border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div>
            <h4 class="text-sm font-bold text-white flex items-center gap-2">
              <span>Official SAR Report Header Logo</span>
            </h4>
            <p class="text-[11px] text-slate-400">Prints on the formal NBA document evaluation cover</p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 p-1 flex items-center justify-center shrink-0">
            <img v-if="form.report_header_logo" :src="form.report_header_logo" class="w-full h-full object-contain rounded-lg" />
            <span v-else class="text-xs font-bold text-purple-400">PDF</span>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Report Header Logo URL</label>
            <input type="text" v-model="form.report_header_logo" placeholder="https://example.com/letterhead-logo.png"
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div>
            <label class="font-semibold text-slate-300 block mb-1">Upload Header Logo</label>
            <input type="file" @change="uploadImage($event, 'report_header_logo')" accept="image/*"
                   class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600/20 file:text-indigo-300 hover:file:bg-indigo-600/30 file:cursor-pointer cursor-pointer" />
          </div>
        </div>
      </div>
    </div>

    <!-- Tab 2: 🏛️ Institution & Accreditation Profile -->
    <div v-if="activeSettingTab === 'institution'" class="glass-card rounded-xl p-6 border border-slate-800 space-y-4 text-xs">
      <h4 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
        <AppIcon name="academic" size="14" />
        <span>Institutional Registration & Governance Info</span>
      </h4>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-semibold text-slate-300">Institution Name</label>
          <input type="text" v-model="form.institution_name"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
        <div>
          <label class="font-semibold text-slate-300">Institution Code</label>
          <input type="text" v-model="form.institution_code"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-semibold text-slate-300">Head of Institution (Principal / Director)</label>
          <input type="text" v-model="form.head_of_institution"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
        <div>
          <label class="font-semibold text-slate-300">Official Website URL</label>
          <input type="text" v-model="form.institution_website"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
      </div>

      <div>
        <label class="font-semibold text-slate-300">Campus Address</label>
        <textarea v-model="form.institution_address" rows="2"
                  class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none"></textarea>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-800">
        <div>
          <label class="font-semibold text-indigo-300">NBA Accreditation Tier</label>
          <select v-model="form.nba_tier"
                  class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
            <option value="Tier-II">Tier-II (Non-Autonomous Engineering)</option>
            <option value="Tier-I">Tier-I (Autonomous / University Dept)</option>
          </select>
        </div>
        <div>
          <label class="font-semibold text-emerald-300">Active Cycle (CAY)</label>
          <input type="text" v-model="form.current_academic_year" placeholder="e.g. 2024-25 (CAY)"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
        <div>
          <label class="font-semibold text-slate-300">Official Contact Email</label>
          <input type="email" v-model="form.contact_email"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
      </div>
    </div>

    <!-- Tab 3: ⚙️ Portal Titles & Text -->
    <div v-if="activeSettingTab === 'general'" class="glass-card rounded-xl p-6 border border-slate-800 space-y-4 text-xs">
      <h4 class="text-sm font-bold text-white border-b border-slate-800 pb-3 flex items-center gap-2">
        <AppIcon name="settings" size="14" />
        <span>Portal Branding Titles & Theme</span>
      </h4>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="font-semibold text-slate-300">Platform Full Title</label>
          <input type="text" v-model="form.app_name"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
        <div>
          <label class="font-semibold text-slate-300">Short Name / Initials</label>
          <input type="text" v-model="form.app_short_name" placeholder="e.g. NBA"
                 class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
        </div>
      </div>

      <div>
        <label class="font-semibold text-slate-300">Platform Tagline / Description</label>
        <input type="text" v-model="form.app_tagline"
               class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
      </div>
    </div>

    <!-- Tab 4: 🚨 Danger Zone • System Reset & Clean Slate -->
    <div v-if="activeSettingTab === 'reset'" class="glass-card rounded-2xl p-6 border border-rose-500/30 bg-slate-950 space-y-6">
      <div class="border-b border-slate-800 pb-4">
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-rose-500/10 text-rose-400 text-xs font-bold border border-rose-500/30">
          🚨 Super Admin Data Purge
        </div>
        <h4 class="text-base font-bold text-white mt-1.5">
          System Clean Slate & Sample Data Reset
        </h4>
        <p class="text-xs text-slate-400 mt-1">
          Truncate all evaluation scores, mark entries, course outcomes, and student batches so another institution or department can start fresh from scratch.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <!-- Preserved Card -->
        <div class="p-4 rounded-xl bg-slate-900 border border-emerald-500/30 space-y-2">
          <div class="font-bold text-emerald-400 flex items-center gap-2">
            <span>🛡️</span>
            <span>Preserved (Not Deleted)</span>
          </div>
          <ul class="list-disc list-inside text-slate-300 space-y-1 text-[11px]">
            <li>All Staff & User login accounts (Super Admin, HOD, Faculty, IQAC)</li>
            <li>Department, Institution, and Program baseline shells</li>
            <li>System Branding & Portal configuration settings</li>
            <li>Platform login passwords and active roles</li>
          </ul>
        </div>

        <!-- Truncated Card -->
        <div class="p-4 rounded-xl bg-slate-900 border border-rose-500/30 space-y-2">
          <div class="font-bold text-rose-400 flex items-center gap-2">
            <span>🗑️</span>
            <span>Cleared / Reset to Clean Slate (0)</span>
          </div>
          <ul class="list-disc list-inside text-slate-300 space-y-1 text-[11px]">
            <li>Courses & Course Outcomes (CO1 to CO5)</li>
            <li>CO-PO Articulation Matrix mappings</li>
            <li>CIE/SEE student assessment marks & survey ratings</li>
            <li>Faculty FDPs, publications & academic year records</li>
            <li>Financial budget line items & SAR claimed score cache</li>
          </ul>
        </div>
      </div>

      <div class="p-4 rounded-xl bg-slate-900/80 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
          <h5 class="text-xs font-bold text-white">System Data Actions</h5>
          <p class="text-[11px] text-slate-400 mt-0.5">
            Purge sample data for fresh deployment, or restore demo syllabus & evaluation data anytime.
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <button @click="openConfirmModal('restore')"
                  class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition">
            <span>🌱</span>
            <span>Restore Sample Demo Data</span>
          </button>

          <button @click="openConfirmModal('reset')"
                  class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-lg shadow-rose-600/30 flex items-center gap-2 transition">
            <span>🗑️</span>
            <span>Reset to Clean Slate</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Password Confirmation Modal for Data Reset / Restore -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
      <div class="glass-card bg-slate-900 border rounded-2xl w-full max-w-md p-6 space-y-4 shadow-2xl"
           :class="modalMode === 'reset' ? 'border-rose-500/40' : 'border-emerald-500/40'">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <div class="flex items-center gap-2">
            <span class="text-lg">{{ modalMode === 'reset' ? '⚠️' : '🌱' }}</span>
            <h4 class="text-sm font-bold text-white">
              {{ modalMode === 'reset' ? 'Confirm System Clean Slate Purge' : 'Confirm Sample Demo Data Restore' }}
            </h4>
          </div>
          <button @click="showModal = false" class="text-slate-400 hover:text-white">✕</button>
        </div>

        <p class="text-xs text-slate-300 leading-relaxed">
          <span v-if="modalMode === 'reset'">
            You are about to truncate all courses, assessment marks, student batches, and evaluation scores. <strong class="text-white">Staff logins will be preserved.</strong>
          </span>
          <span v-else>
            This will populate your system with full demo courses, CO-PO articulation matrices, mark entries, SFR/FQI records, and Tier-II SAR criteria.
          </span>
        </p>

        <form @submit.prevent="executeAction" class="space-y-4 text-xs">
          <div>
            <label class="font-semibold text-slate-300 block mb-1">Enter Super Admin Password to Authorize:</label>
            <input type="password" v-model="adminPassword" required placeholder="••••••••"
                   class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2.5 text-white focus:ring-1 outline-none"
                   :class="modalMode === 'reset' ? 'focus:border-rose-500 focus:ring-rose-500' : 'focus:border-emerald-500 focus:ring-emerald-500'" />
          </div>

          <div v-if="actionError" class="p-2.5 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
            {{ actionError }}
          </div>

          <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" @click="showModal = false" class="px-3.5 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 font-semibold">
              Cancel
            </button>
            <button type="submit" :disabled="processing"
                    class="px-5 py-2 rounded-lg text-white font-bold transition disabled:opacity-50 flex items-center gap-2"
                    :class="modalMode === 'reset' ? 'bg-rose-600 hover:bg-rose-500' : 'bg-emerald-600 hover:bg-emerald-500'">
              <span v-if="processing" class="w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
              <span>{{ processing ? 'Processing...' : (modalMode === 'reset' ? 'Confirm & Purge Data' : 'Confirm & Restore Data') }}</span>
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
  name: 'SettingsView',
  components: { AppIcon },
  data() {
    return {
      activeSettingTab: 'branding',
      saving: false,
      showModal: false,
      modalMode: 'reset', // 'reset' or 'restore'
      processing: false,
      adminPassword: '',
      actionError: '',
      successMsg: '',
      settingTabs: [
        { id: 'branding', label: 'Logos, Photos & Favicon', icon: 'settings' },
        { id: 'institution', label: 'Institution Profile', icon: 'academic' },
        { id: 'general', label: 'Portal Titles', icon: 'settings' },
        { id: 'reset', label: 'System Clean Slate & Reset', icon: 'workflow' },
      ],
      form: {
        app_name: '',
        app_short_name: '',
        app_tagline: '',
        institution_name: '',
        institution_code: '',
        head_of_institution: '',
        institution_address: '',
        institution_website: '',
        contact_email: '',
        contact_phone: '',
        nba_tier: 'Tier-II',
        current_academic_year: '2024-25 (CAY)',
        login_logo: '',
        sidebar_logo: '',
        favicon: '',
        report_header_logo: '',
      },
    };
  },
  mounted() {
    this.fetchSettings();
  },
  methods: {
    async fetchSettings() {
      try {
        const res = await axios.get('/api/v1/settings');
        if (res.data && res.data.success) {
          this.form = { ...this.form, ...res.data.data };
          this.updateFavicon(this.form.favicon);
        }
      } catch (err) {
        console.error('Failed to load settings:', err);
      }
    },
    async saveSettings() {
      this.saving = true;
      this.successMsg = '';
      try {
        const res = await axios.post('/api/v1/settings', this.form);
        if (res.data && res.data.success) {
          this.successMsg = 'Settings and branding updated successfully!';
          this.form = { ...this.form, ...res.data.data };
          this.updateFavicon(this.form.favicon);
          this.$emit('settings-updated', this.form);
        }
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to save settings');
      } finally {
        this.saving = false;
      }
    },
    async uploadImage(event, type) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('image', file);
      formData.append('type', type);

      try {
        const res = await axios.post('/api/v1/settings/upload-image', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
        if (res.data && res.data.success) {
          this.form[type] = res.data.url;
          if (type === 'favicon') {
            this.updateFavicon(res.data.url);
          }
          this.successMsg = `Uploaded ${type.replace(/_/g, ' ')} successfully!`;
        }
      } catch (err) {
        alert(err.response?.data?.message || 'Image upload failed');
      }
    },
    updateFavicon(url) {
      if (!url) return;
      let link = document.querySelector("link[rel~='icon']");
      if (!link) {
        link = document.createElement('link');
        link.rel = 'icon';
        document.getElementsByTagName('head')[0].appendChild(link);
      }
      link.href = url;
    },
    openConfirmModal(mode) {
      this.modalMode = mode;
      this.adminPassword = '';
      this.actionError = '';
      this.showModal = true;
    },
    async executeAction() {
      this.processing = true;
      this.actionError = '';
      const endpoint = this.modalMode === 'reset'
        ? '/api/v1/settings/reset-academic-data'
        : '/api/v1/settings/restore-sample-data';

      try {
        const res = await axios.post(endpoint, {
          password: this.adminPassword,
        });

        if (res.data && res.data.success) {
          this.showModal = false;
          this.adminPassword = '';
          this.successMsg = res.data.message;
          this.$emit('settings-updated', this.form);
        } else {
          this.actionError = res.data.message || 'Action failed.';
        }
      } catch (err) {
        this.actionError = err.response?.data?.message || 'Invalid password or server error.';
      } finally {
        this.processing = false;
      }
    },
  },
};
</script>
