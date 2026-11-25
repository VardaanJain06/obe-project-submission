<template>
  <div class="space-y-6">
    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 text-xs font-semibold border border-indigo-500/20">
          👑 Super Admin & HOD Control Panel
        </div>
        <h3 class="text-xl font-bold text-white mt-1">
          Staff & Faculty Directory Management
        </h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Create, assign department roles, update qualifications, and configure platform user permissions.
        </p>
      </div>

      <div>
        <button @click="openCreateModal"
                class="px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Add New Staff / Faculty
        </button>
      </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="glass-card rounded-xl p-4 border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="w-full md:w-80 relative">
        <input type="text" v-model="searchQuery" @input="fetchStaff" placeholder="Search by name, email, designation..."
               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 outline-none" />
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
        <!-- Role Filter -->
        <select v-model="selectedRole" @change="fetchStaff"
                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
          <option value="">All Roles</option>
          <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
        </select>

        <!-- Department Filter -->
        <select v-model="selectedDepartment" @change="fetchStaff"
                class="bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
          <option value="">All Departments</option>
          <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>

        <span class="text-xs text-slate-400 font-mono">Total: {{ staffList.length }} Staff</span>
      </div>
    </div>

    <!-- Staff Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-slate-800">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-950/80 text-slate-400 text-[10px] uppercase font-bold border-b border-slate-800">
            <tr>
              <th class="p-3.5">Staff Member</th>
              <th class="p-3.5">Role & Access</th>
              <th class="p-3.5">Department</th>
              <th class="p-3.5">Designation</th>
              <th class="p-3.5">Contact</th>
              <th class="p-3.5 text-center">Status</th>
              <th class="p-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60 text-slate-300">
            <tr v-for="s in staffList" :key="s.id" class="hover:bg-slate-800/30 transition">
              <!-- Avatar & Name -->
              <td class="p-3.5 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center font-bold text-white text-xs">
                  {{ s.name.charAt(0) }}
                </div>
                <div>
                  <div class="font-bold text-white text-xs">{{ s.name }}</div>
                  <div class="text-[10px] text-slate-400 font-mono">{{ s.faculty_code || 'N/A' }}</div>
                </div>
              </td>

              <!-- Role -->
              <td class="p-3.5">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border"
                      :class="roleBadgeClass(s.role)">
                  {{ s.role }}
                </span>
              </td>

              <!-- Department -->
              <td class="p-3.5 font-medium text-slate-300">
                {{ s.department ? s.department.name : 'Institution Level' }}
              </td>

              <!-- Designation -->
              <td class="p-3.5 text-slate-300">
                {{ s.designation || 'Staff Member' }}
              </td>

              <!-- Contact -->
              <td class="p-3.5">
                <div class="text-slate-300">{{ s.email }}</div>
                <div class="text-[10px] text-slate-500">{{ s.phone || 'No phone' }}</div>
              </td>

              <!-- Status Toggle -->
              <td class="p-3.5 text-center">
                <button @click="toggleStatus(s)"
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border transition"
                        :class="s.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30'">
                  {{ s.is_active ? 'Active' : 'Deactivated' }}
                </button>
              </td>

              <!-- Actions -->
              <td class="p-3.5 text-right space-x-2">
                <button @click="openEditModal(s)" class="text-indigo-400 hover:text-indigo-300 font-semibold text-[11px]">
                  Edit
                </button>
                <button @click="deleteStaff(s)" class="text-rose-400 hover:text-rose-300 font-semibold text-[11px]">
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create / Edit Staff Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
      <div class="glass-card bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-lg p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
          <h4 class="text-base font-bold text-white">
            {{ isEditing ? 'Edit Staff Profile' : 'Create New Staff / Faculty Account' }}
          </h4>
          <button @click="showModal = false" class="text-slate-400 hover:text-white text-lg">✕</button>
        </div>

        <form @submit.prevent="saveStaff" class="space-y-3 text-xs">
          <div>
            <label class="font-semibold text-slate-300">Full Name</label>
            <input type="text" v-model="form.name" required placeholder="e.g. Dr. Kavita Sharma"
                   class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Email Address</label>
              <input type="email" v-model="form.email" required placeholder="kavita@niet-tech.edu.in"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Password {{ isEditing ? '(Leave blank to keep)' : '' }}</label>
              <input type="password" v-model="form.password" :required="!isEditing" placeholder="••••••••"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">System Role</label>
              <select v-model="form.role" required
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
              </select>
            </div>
            <div>
              <label class="font-semibold text-slate-300">Assigned Department</label>
              <select v-model="form.department_id"
                      class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none">
                <option :value="null">Institution Level (No Specific Dept)</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Designation</label>
              <input type="text" v-model="form.designation" placeholder="e.g. Associate Professor"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Faculty ID / Code</label>
              <input type="text" v-model="form.faculty_code" placeholder="e.g. FAC-CSE-025"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="font-semibold text-slate-300">Highest Qualification</label>
              <input type="text" v-model="form.highest_degree" placeholder="e.g. Ph.D. / M.Tech"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
            <div>
              <label class="font-semibold text-slate-300">Phone Number</label>
              <input type="text" v-model="form.phone" placeholder="+91 9800000000"
                     class="w-full mt-1 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-indigo-500 outline-none" />
            </div>
          </div>

          <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
            <button type="button" @click="showModal = false"
                    class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 font-semibold">
              Cancel
            </button>
            <button type="submit" :disabled="saving"
                    class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-600/30 transition">
              {{ saving ? 'Saving...' : (isEditing ? 'Update Staff Member' : 'Create Staff Member') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'StaffManagementView',
  data() {
    return {
      staffList: [],
      departments: [],
      roles: ['Super Admin', 'HOD', 'Faculty', 'IQAC Admin', 'Evaluator'],
      searchQuery: '',
      selectedRole: '',
      selectedDepartment: '',
      showModal: false,
      isEditing: false,
      saving: false,
      form: {
        id: null,
        name: '',
        email: '',
        password: '',
        role: 'Faculty',
        department_id: null,
        designation: '',
        faculty_code: '',
        phone: '',
        highest_degree: 'M.Tech',
        teaching_experience_years: 3,
        is_active: true,
      },
    };
  },
  mounted() {
    this.fetchStaff();
  },
  methods: {
    async fetchStaff() {
      try {
        const res = await axios.get('/api/v1/staff', {
          params: {
            search: this.searchQuery,
            role: this.selectedRole,
            department_id: this.selectedDepartment,
          },
        });
        if (res.data && res.data.success) {
          this.staffList = res.data.data.staff || [];
          this.departments = res.data.data.departments || [];
        }
      } catch (err) {
        console.error('Failed to load staff list:', err);
      }
    },
    openCreateModal() {
      this.isEditing = false;
      this.form = {
        id: null,
        name: '',
        email: '',
        password: '',
        role: 'Faculty',
        department_id: this.departments.length > 0 ? this.departments[0].id : null,
        designation: 'Assistant Professor',
        faculty_code: 'FAC-CSE-' + Math.floor(100 + Math.random() * 900),
        phone: '',
        highest_degree: 'M.Tech',
        teaching_experience_years: 3,
        is_active: true,
      };
      this.showModal = true;
    },
    openEditModal(staff) {
      this.isEditing = true;
      this.form = {
        id: staff.id,
        name: staff.name,
        email: staff.email,
        password: '',
        role: staff.role,
        department_id: staff.department_id,
        designation: staff.designation || '',
        faculty_code: staff.faculty_code || '',
        phone: staff.phone || '',
        highest_degree: staff.faculty_profile?.highest_degree || 'M.Tech',
        teaching_experience_years: staff.faculty_profile?.teaching_experience_years || 3,
        is_active: staff.is_active,
      };
      this.showModal = true;
    },
    async saveStaff() {
      this.saving = true;
      try {
        if (this.isEditing) {
          await axios.put(`/api/v1/staff/${this.form.id}`, this.form);
        } else {
          await axios.post('/api/v1/staff', this.form);
        }
        this.showModal = false;
        this.fetchStaff();
      } catch (err) {
        alert(err.response?.data?.message || 'Failed to save staff');
      } finally {
        this.saving = false;
      }
    },
    async toggleStatus(staff) {
      try {
        await axios.post(`/api/v1/staff/${staff.id}/toggle-status`);
        this.fetchStaff();
      } catch (err) {
        console.error('Failed to toggle status:', err);
      }
    },
    async deleteStaff(staff) {
      if (!confirm(`Are you sure you want to remove ${staff.name}?`)) return;
      try {
        await axios.delete(`/api/v1/staff/${staff.id}`);
        this.fetchStaff();
      } catch (err) {
        alert(err.response?.data?.message || 'Delete failed');
      }
    },
    roleBadgeClass(role) {
      switch (role) {
        case 'Super Admin':
          return 'bg-purple-500/10 text-purple-400 border-purple-500/30';
        case 'HOD':
          return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'IQAC Admin':
          return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        case 'Evaluator':
          return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        default:
          return 'bg-slate-800 text-slate-300 border-slate-700';
      }
    },
  },
};
</script>
