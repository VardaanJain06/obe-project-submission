<template>
  <div class="academic-audit-page">
    <div class="page-header">
      <div>
        <h2>Academic Audits</h2>
        <p>Manage internal and external academic audits.</p>
      </div>

      <button class="primary-btn" @click="openAddModal">
        + Add Audit
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-card">
      <div class="filter-group">
        <label>Audit Type</label>
        <select v-model="filters.audit_type" @change="fetchAudits">
          <option value="">All</option>
          <option value="internal">Internal</option>
          <option value="external">External</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Status</label>
        <select v-model="filters.status" @change="fetchAudits">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <div class="filter-group">
        <label>Date From</label>
        <input
          type="date"
          v-model="filters.date_from"
          @change="fetchAudits"
        />
      </div>

      <div class="filter-group">
        <label>Date To</label>
        <input
          type="date"
          v-model="filters.date_to"
          @change="fetchAudits"
        />
      </div>

      <button class="secondary-btn" @click="resetFilters">
        Reset
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="state-message">
      Loading academic audits...
    </div>

    <!-- Empty -->
    <div v-else-if="audits.length === 0" class="state-message">
      No academic audits found.
    </div>

    <!-- Audit Table -->
    <div v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Status</th>
            <th>Audit Date</th>
            <th>Auditor</th>
            <th>Findings</th>
            <th>Recommendations</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="audit in audits" :key="audit.id">
            <td>
              <strong>{{ audit.title }}</strong>
            </td>

            <td>
              <span
                class="type-badge"
                :class="audit.audit_type"
              >
                {{ capitalize(audit.audit_type) }}
              </span>
            </td>

            <td>
              <span
                class="status-badge"
                :class="audit.status"
              >
                {{ capitalize(audit.status) }}
              </span>
            </td>

            <td>{{ formatDate(audit.audit_date) }}</td>

            <td>{{ audit.auditor_name || '-' }}</td>

            <td class="text-cell">
              {{ audit.findings || '-' }}
            </td>

            <td class="text-cell">
              {{ audit.recommendations || '-' }}
            </td>

            <td>
              <div class="action-buttons">
                <button
                  class="edit-btn"
                  @click="openEditModal(audit)"
                >
                  Edit
                </button>

                <button
                  class="delete-btn"
                  @click="deleteAudit(audit.id)"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="modal-overlay"
      @click.self="closeModal"
    >
      <div class="modal">
        <div class="modal-header">
          <div>
            <h3>
              {{ editingAudit ? 'Edit Academic Audit' : 'Add Academic Audit' }}
            </h3>
            <p>Enter audit details below.</p>
          </div>

          <button class="close-btn" @click="closeModal">
            ×
          </button>
        </div>

        <form @submit.prevent="saveAudit">
          <div class="form-grid">
            <div class="form-group full-width">
              <label>Title</label>
              <input
                type="text"
                v-model="form.title"
                placeholder="e.g. Internal Academic Audit - CSE"
                required
              />
            </div>

            <div class="form-group">
              <label>Audit Type</label>
              <select v-model="form.audit_type" required>
                <option value="internal">Internal</option>
                <option value="external">External</option>
              </select>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select v-model="form.status" required>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div class="form-group">
              <label>Audit Date</label>
              <input
                type="date"
                v-model="form.audit_date"
                required
              />
            </div>

            <div class="form-group">
              <label>Auditor Name</label>
              <input
                type="text"
                v-model="form.auditor_name"
                placeholder="e.g. NBA Coordinator"
              />
            </div>

            <div class="form-group full-width">
              <label>Findings</label>
              <textarea
                v-model="form.findings"
                rows="4"
                placeholder="Enter audit findings..."
              ></textarea>
            </div>

            <div class="form-group full-width">
              <label>Recommendations</label>
              <textarea
                v-model="form.recommendations"
                rows="4"
                placeholder="Enter recommendations..."
              ></textarea>
            </div>
          </div>

          <div class="modal-actions">
            <button
              type="button"
              class="secondary-btn"
              @click="closeModal"
            >
              Cancel
            </button>

            <button
              type="submit"
              class="primary-btn"
              :disabled="saving"
            >
              {{ saving ? 'Saving...' : (editingAudit ? 'Update Audit' : 'Save Audit') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AcademicAuditView',

  data() {
    return {
      audits: [],
      loading: false,
      saving: false,

      showModal: false,
      editingAudit: null,

      filters: {
        audit_type: '',
        status: '',
        date_from: '',
        date_to: '',
      },

      form: {
        title: '',
        audit_type: 'internal',
        status: 'pending',
        audit_date: '',
        findings: '',
        recommendations: '',
        auditor_name: '',
      },
    };
  },

  mounted() {
    this.fetchAudits();
  },

  methods: {
    async fetchAudits() {
      this.loading = true;

      try {
        const params = new URLSearchParams();

        if (this.filters.audit_type) {
          params.append('audit_type', this.filters.audit_type);
        }

        if (this.filters.status) {
          params.append('status', this.filters.status);
        }

        if (this.filters.date_from) {
          params.append('date_from', this.filters.date_from);
        }

        if (this.filters.date_to) {
          params.append('date_to', this.filters.date_to);
        }

        const query = params.toString();

        const response = await fetch(
          `/api/v1/academic-audits${query ? `?${query}` : ''}`
        );

        if (!response.ok) {
          throw new Error('Failed to fetch academic audits.');
        }

        const result = await response.json();

        this.audits = result.data || [];
      } catch (error) {
        console.error(error);
        alert('Failed to load academic audits.');
      } finally {
        this.loading = false;
      }
    },

    openAddModal() {
      this.editingAudit = null;

      this.form = {
        title: '',
        audit_type: 'internal',
        status: 'pending',
        audit_date: new Date().toISOString().split('T')[0],
        findings: '',
        recommendations: '',
        auditor_name: '',
      };

      this.showModal = true;
    },

    openEditModal(audit) {
      this.editingAudit = audit;

      this.form = {
        title: audit.title || '',
        audit_type: audit.audit_type || 'internal',
        status: audit.status || 'pending',
        audit_date: audit.audit_date
          ? audit.audit_date.substring(0, 10)
          : '',
        findings: audit.findings || '',
        recommendations: audit.recommendations || '',
        auditor_name: audit.auditor_name || '',
      };

      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.editingAudit = null;
    },

    async saveAudit() {
      this.saving = true;

      try {
        const isEditing = !!this.editingAudit;

        const url = isEditing
          ? `/api/v1/academic-audits/${this.editingAudit.id}`
          : '/api/v1/academic-audits';

        const method = isEditing ? 'PUT' : 'POST';

        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          body: JSON.stringify(this.form),
        });

        const result = await response.json();

        if (!response.ok) {
          throw new Error(
            result.message || 'Failed to save academic audit.'
          );
        }

        this.closeModal();
        await this.fetchAudits();

        alert(
          isEditing
            ? 'Academic audit updated successfully.'
            : 'Academic audit created successfully.'
        );
      } catch (error) {
        console.error(error);
        alert(error.message || 'Failed to save academic audit.');
      } finally {
        this.saving = false;
      }
    },

    async deleteAudit(id) {
      if (!confirm('Are you sure you want to delete this academic audit?')) {
        return;
      }

      try {
        const response = await fetch(
          `/api/v1/academic-audits/${id}`,
          {
            method: 'DELETE',
            headers: {
              Accept: 'application/json',
            },
          }
        );

        const result = await response.json();

        if (!response.ok) {
          throw new Error(
            result.message || 'Failed to delete academic audit.'
          );
        }

        await this.fetchAudits();

        alert('Academic audit deleted successfully.');
      } catch (error) {
        console.error(error);
        alert(error.message || 'Failed to delete academic audit.');
      }
    },

    resetFilters() {
      this.filters = {
        audit_type: '',
        status: '',
        date_from: '',
        date_to: '',
      };

      this.fetchAudits();
    },

    formatDate(date) {
      if (!date) {
        return '-';
      }

      return new Date(date).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
      });
    },

    capitalize(value) {
      if (!value) {
        return '';
      }

      return value.charAt(0).toUpperCase() + value.slice(1);
    },
  },
};
</script>

<style scoped>
.academic-audit-page {
  padding: 24px;
  color: #e2e8f0;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h2 {
  margin: 0 0 6px;
  font-size: 26px;
}

.page-header p {
  margin: 0;
  color: #94a3b8;
}

.primary-btn,
.secondary-btn,
.edit-btn,
.delete-btn {
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  cursor: pointer;
  font-weight: 600;
}

.primary-btn {
  background: #2563eb;
  color: white;
}

.primary-btn:hover {
  background: #1d4ed8;
}

.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.secondary-btn {
  background: #334155;
  color: #e2e8f0;
}

.secondary-btn:hover {
  background: #475569;
}

.filters-card {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: end;
  padding: 18px;
  margin-bottom: 20px;
  background: #111827;
  border: 1px solid #1e293b;
  border-radius: 12px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-group label,
.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
}

.filter-group select,
.filter-group input,
.form-group input,
.form-group select,
.form-group textarea {
  background: #0f172a;
  color: #e2e8f0;
  border: 1px solid #334155;
  border-radius: 7px;
  padding: 9px 11px;
  outline: none;
}

.filter-group select:focus,
.filter-group input:focus,
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #3b82f6;
}

.table-card {
  overflow-x: auto;
  background: #111827;
  border: 1px solid #1e293b;
  border-radius: 12px;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1100px;
}

th,
td {
  padding: 14px 16px;
  border-bottom: 1px solid #1e293b;
  text-align: left;
  vertical-align: top;
}

th {
  background: #0f172a;
  color: #94a3b8;
  font-size: 12px;
  text-transform: uppercase;
}

td {
  color: #cbd5e1;
  font-size: 14px;
}

.text-cell {
  max-width: 220px;
  line-height: 1.5;
}

.type-badge,
.status-badge {
  display: inline-block;
  padding: 5px 9px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.type-badge.internal {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.type-badge.external {
  background: rgba(168, 85, 247, 0.15);
  color: #c084fc;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
}

.status-badge.completed {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.edit-btn {
  background: #1d4ed8;
  color: white;
}

.delete-btn {
  background: #991b1b;
  color: white;
}

.state-message {
  padding: 50px;
  text-align: center;
  color: #94a3b8;
  background: #111827;
  border: 1px solid #1e293b;
  border-radius: 12px;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 1000;
}

.modal {
  width: min(760px, 100%);
  max-height: 90vh;
  overflow-y: auto;
  background: #111827;
  border: 1px solid #334155;
  border-radius: 14px;
  padding: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 22px;
}

.modal-header h3 {
  margin: 0 0 5px;
  font-size: 21px;
}

.modal-header p {
  margin: 0;
  color: #94a3b8;
  font-size: 13px;
}

.close-btn {
  border: none;
  background: transparent;
  color: #94a3b8;
  font-size: 28px;
  cursor: pointer;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.full-width {
  grid-column: 1 / -1;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #1e293b;
}

@media (max-width: 700px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: auto;
  }
}
</style>