<template>
    <div class="survey-page">
        <div class="survey-header">
            <div>
                <h2>Stakeholder Surveys</h2>
                <p>
                    Manage Graduate Exit, Alumni and Employer survey responses.
                </p>
            </div>

            <button class="primary-btn" @click="showForm = true">
                + Add Survey
            </button>
        </div>

        <div class="survey-card">
            <div v-if="loading" class="empty-state">
                Loading survey records...
            </div>

            <div v-else-if="surveys.length === 0" class="empty-state">
                No survey records found.
            </div>

            <table v-else class="survey-table">
                <thead>
                    <tr>
                        <th>Survey Type</th>
                        <th>Program</th>
                        <th>Target</th>
                        <th>Rating</th>
                        <th>Respondents</th>
                        <th>Academic Year</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="survey in surveys" :key="survey.id">
                        <td>{{ formatSurveyType(survey.survey_type) }}</td>

                        <td>
                            {{ survey.program?.name ?? survey.program_id }}
                        </td>

                        <td>
    {{ getTargetLabel(survey) }}
</td>

                        <td>{{ survey.average_rating }}</td>

                        <td>{{ survey.total_respondents }}</td>

                        <td>
                            {{ survey.academic_year?.name ?? survey.academic_year_id }}
                        </td>

                        <td>
                            <button
                                class="action-btn edit"
                                @click="editSurvey(survey)"
                            >
                                Edit
                            </button>

                            <button
                                class="action-btn delete"
                                @click="deleteSurvey(survey.id)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="showForm" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <h3>{{ editingSurvey ? 'Edit Survey' : 'Add Survey' }}</h3>

                    <button class="close-btn" @click="closeForm">
                        ×
                    </button>
                </div>

                <form @submit.prevent="saveSurvey">
                    <div class="form-group">
                        <label>Survey Type</label>

                        <select v-model="form.survey_type" required>
                            <option value="Graduate_Exit_Survey">
                                Graduate Exit Survey
                            </option>

                            <option value="Alumni_Survey">
                                Alumni Survey
                            </option>

                            <option value="Employer_Survey">
                                Employer Survey
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
    <label>Program</label>

    <select
        v-model.number="form.program_id"
        required
        :disabled="optionsLoading"
    >
        <option value="" disabled>
            {{ optionsLoading ? 'Loading programs...' : 'Select Program' }}
        </option>

        <option
            v-for="program in programs"
            :key="program.id"
            :value="program.id"
        >
            {{ program.code }} - {{ program.name }}
        </option>
    </select>
</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Target Type</label>

                            <select v-model="form.target_type" required>
                                <option value="PO">PO</option>
                                <option value="PSO">PSO</option>
                            </select>
                        </div>

                        <div class="form-group">
    <label>Target</label>

    <select
        v-model.number="form.target_id"
        required
        :disabled="!form.program_id || optionsLoading"
    >
        <option value="" disabled>
            {{ form.program_id ? 'Select Target' : 'Select Program first' }}
        </option>

        <option
            v-for="target in selectedTargets"
            :key="target.id"
            :value="target.id"
        >
            {{ target.code }} - {{ target.title }}
        </option>
    </select>
</div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Average Rating</label>

                            <input
                                v-model.number="form.average_rating"
                                type="number"
                                min="1"
                                max="3"
                                step="0.01"
                                required
                            />
                        </div>

                        <div class="form-group">
                            <label>Total Respondents</label>

                            <input
                                v-model.number="form.total_respondents"
                                type="number"
                                min="1"
                                required
                            />
                        </div>
                    </div>

                    <div v-if="error" class="error-message">
                        {{ error }}
                    </div>

                    <div class="modal-actions">
                        <button
                            type="button"
                            class="secondary-btn"
                            @click="closeForm"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="primary-btn"
                            :disabled="saving"
                        >
                            {{ saving ? 'Saving...' : 'Save Survey' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
const surveys = ref([])
const programs = ref([])
const loading = ref(false)
const saving = ref(false)
const editingId = ref(null)
const optionsLoading = ref(false)
const error = ref('')

const showForm = ref(false)
const editingSurvey = ref(null)

const form = ref({
    program_id: '',
    survey_type: 'Graduate_Exit_Survey',
    target_type: 'PO',
    target_id: '',
    average_rating: '',
    total_respondents: 50,
})
const selectedTargets = computed(() => {
    const program = programs.value.find(
        p => p.id === Number(form.value.program_id)
    )

    if (!program) {
        return []
    }

    if (form.value.target_type === 'PO') {
        return (program.program_outcomes ?? []).map(po => ({
            id: po.id,
            code: po.po_code,
            title: po.title,
        }))
    }

    return (program.program_specific_outcomes ?? []).map(pso => ({
        id: pso.id,
        code: pso.pso_code,
        title: pso.title,
    }))
})
watch(
    () => form.value.target_type,
    () => {
        form.value.target_id = ''
    }
)
const fetchSurveyOptions = async () => {
    optionsLoading.value = true

    try {
        const response = await fetch('/api/v1/attainment/survey-options')
        const result = await response.json()

        if (!response.ok) {
            throw new Error(result.message || 'Failed to load survey options.')
        }

        programs.value = result.data ?? []
    } catch (err) {
        error.value = err.message
    } finally {
        optionsLoading.value = false
    }
}
const fetchSurveys = async () => {
    loading.value = true
    error.value = ''

    try {
        const response = await fetch('/api/v1/attainment/surveys')
        const result = await response.json()

        if (!response.ok) {
            throw new Error(result.message || 'Failed to load surveys.')
        }

        surveys.value = result.data ?? []
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
}
const getTargetLabel = (survey) => {
    const program = programs.value.find(
        p => p.id === Number(survey.program_id)
    )

    if (!program) {
        return `${survey.target_type} #${survey.target_id}`
    }

    const targets = survey.target_type === 'PO'
        ? (program.program_outcomes ?? [])
        : (program.program_specific_outcomes ?? [])

    const target = targets.find(
        item => item.id === Number(survey.target_id)
    )

    if (!target) {
        return `${survey.target_type} #${survey.target_id}`
    }

    const code = survey.target_type === 'PO'
        ? target.po_code
        : target.pso_code

    return `${code} - ${target.title}`
}
const saveSurvey = async () => {
    saving.value = true
    error.value = ''

    try {
        const isEditing = !!editingId.value

        const url = isEditing
            ? `/api/v1/attainment/surveys/${editingId.value}`
            : '/api/v1/attainment/record-survey'

        const response = await fetch(url, {
            method: isEditing ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value),
        })

        const result = await response.json()

        if (!response.ok) {
            throw new Error(result.message || 'Failed to save survey.')
        }

        closeForm()
        await fetchSurveys()
    } catch (err) {
        error.value = err.message
    } finally {
        saving.value = false
    }
}

const editSurvey = (survey) => {
    editingSurvey.value = survey

    form.value = {
        program_id: survey.program_id,
        survey_type: survey.survey_type,
        target_type: survey.target_type,
        target_id: survey.target_id,
        average_rating: survey.average_rating,
        total_respondents: survey.total_respondents,
    }

    showForm.value = true
}

const deleteSurvey = async (id) => {
    if (!confirm('Are you sure you want to delete this survey record?')) {
        return
    }

    try {
        const response = await fetch(`/api/v1/attainment/surveys/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
            },
        })

        const result = await response.json()

        if (!response.ok) {
            throw new Error(result.message || 'Failed to delete survey.')
        }

        await fetchSurveys()
    } catch (err) {
        error.value = err.message
    }
}

const closeForm = () => {
    showForm.value = false
    editingSurvey.value = null
    error.value = ''

    form.value = {
        program_id: '',
        survey_type: 'Graduate_Exit_Survey',
        target_type: 'PO',
        target_id: '',
        average_rating: '',
        total_respondents: 50,
    }
}

const formatSurveyType = (type) => {
    const labels = {
        Graduate_Exit_Survey: 'Graduate Exit Survey',
        Alumni_Survey: 'Alumni Survey',
        Employer_Survey: 'Employer Survey',
    }

    return labels[type] ?? type
}

onMounted(() => {
    fetchSurveys()
    fetchSurveyOptions()
})
</script>

<style scoped>
.survey-page {
    padding: 4px;
    color: #e2e8f0;
}

.survey-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
}

.survey-header h2 {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #f8fafc;
}

.survey-header p {
    margin: 0;
    color: #94a3b8;
    font-size: 13px;
}

.survey-card {
    background: rgba(15, 23, 42, 0.75);
    border: 1px solid #1e293b;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.18);
    overflow-x: auto;
}

.survey-table {
    width: 100%;
    border-collapse: collapse;
}

.survey-table th {
    padding: 12px;
    border-bottom: 1px solid #334155;
    color: #94a3b8;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    text-align: left;
}

.survey-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #1e293b;
    color: #cbd5e1;
    font-size: 12px;
    text-align: left;
}

.survey-table tbody tr:hover {
    background: rgba(30, 41, 59, 0.45);
}

.primary-btn,
.secondary-btn,
.action-btn {
    border: 1px solid transparent;
    border-radius: 7px;
    padding: 9px 14px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.primary-btn {
    background: #4f46e5;
    color: white;
    border-color: #6366f1;
}

.primary-btn:hover {
    background: #4338ca;
}

.primary-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.secondary-btn {
    background: #1e293b;
    color: #cbd5e1;
    border-color: #334155;
}

.secondary-btn:hover {
    background: #334155;
    color: white;
}

.action-btn {
    padding: 7px 11px;
}

.action-btn.edit {
    background: #1e293b;
    color: #cbd5e1;
    border-color: #334155;
}

.action-btn.edit:hover {
    background: #334155;
    color: white;
}

.action-btn.delete {
    background: rgba(127, 29, 29, 0.25);
    color: #fca5a5;
    border-color: rgba(239, 68, 68, 0.25);
}

.action-btn.delete:hover {
    background: rgba(127, 29, 29, 0.45);
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #64748b;
    font-size: 13px;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(2, 6, 23, 0.75);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    width: 520px;
    max-width: 90%;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
}

.modal-header h3 {
    margin: 0;
    color: #f8fafc;
    font-size: 16px;
    font-weight: 700;
}

.close-btn {
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 24px;
    line-height: 1;
    cursor: pointer;
}

.close-btn:hover {
    color: #f8fafc;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: #cbd5e1;
    font-size: 12px;
    font-weight: 600;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 11px;
    background: #020617;
    color: #e2e8f0;
    border: 1px solid #334155;
    border-radius: 7px;
    outline: none;
    font-size: 12px;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
}

.form-group input:disabled,
.form-group select:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
}

.error-message {
    color: #fca5a5;
    background: rgba(127, 29, 29, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.25);
    border-radius: 7px;
    padding: 10px;
    margin-top: 10px;
    font-size: 12px;
}

@media (max-width: 640px) {
    .survey-header {
        flex-direction: column;
        gap: 15px;
    }

    .form-row {
        grid-template-columns: 1fr;
        gap: 0;
    }
}
</style>
