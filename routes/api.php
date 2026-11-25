<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StaffManagementApiController;
use App\Http\Controllers\Api\V1\SettingsApiController;
use App\Http\Controllers\Api\V1\CourseManagementApiController;
use App\Http\Controllers\Api\V1\AssessmentEntryApiController;
use App\Http\Controllers\Api\V1\NbaDashboardApiController;
use App\Http\Controllers\Api\V1\SfrCalculatorApiController;
use App\Http\Controllers\Api\V1\FqiCalculatorApiController;
use App\Http\Controllers\Api\V1\ArticulationMatrixApiController;
use App\Http\Controllers\Api\V1\AttainmentApiController;
use App\Http\Controllers\Api\V1\SarWorkflowApiController;
use App\Http\Controllers\Api\V1\SarReportExportApiController;
use App\Http\Controllers\Api\V1\StudentMarksCsvController;
use App\Http\Controllers\Api\V1\AssessmentBlueprintController;
use App\Http\Controllers\Api\V1\CriterionScoreApiController;
use App\Http\Controllers\Api\V1\AcademicAuditApiController;

Route::prefix('v1')->group(function () {

    // 1. Authentication & Session
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // 2. Staff & Faculty Management (Super Admin & HOD)
    Route::prefix('staff')->group(function () {
        Route::get('/', [StaffManagementApiController::class, 'index']);
        Route::post('/', [StaffManagementApiController::class, 'store']);
        Route::put('/{id}', [StaffManagementApiController::class, 'update']);
        Route::post('/{id}/toggle-status', [StaffManagementApiController::class, 'toggleStatus']);
        Route::delete('/{id}', [StaffManagementApiController::class, 'destroy']);

        Route::post(
            '/assessments/{assessmentBatch}/marks/upload',
            [StudentMarksCsvController::class, 'upload']
        );

        Route::post(
            '/assessments/blueprint',
            [AssessmentBlueprintController::class, 'store']
        );

        Route::get(
            '/assessments/{assessmentBatch}/blueprint',
            [AssessmentBlueprintController::class, 'show']
        );
    });

    // 3. System Settings & Branding (Super Admin)
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingsApiController::class, 'index']);
        Route::post('/', [SettingsApiController::class, 'update']);
        Route::post('/upload-image', [SettingsApiController::class, 'uploadImage']);
        Route::post('/reset-academic-data', [SettingsApiController::class, 'resetAcademicData']);
        Route::post('/restore-sample-data', [SettingsApiController::class, 'restoreSampleData']);
    });

    // 4. Curriculum & Course Management CRUD
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseManagementApiController::class, 'index']);
        Route::post('/', [CourseManagementApiController::class, 'store']);
        Route::post('/{id}/co', [CourseManagementApiController::class, 'addCourseOutcome']);
        Route::delete('/{id}', [CourseManagementApiController::class, 'destroy']);
    });

    // 5. Direct & Indirect Attainment Mark Logging
    Route::prefix('attainment')->group(function () {
        Route::post('/record-marks', [AssessmentEntryApiController::class, 'recordMarks']);
        Route::post('/record-survey', [AssessmentEntryApiController::class, 'recordSurvey']);
        Route::get('/surveys', [AssessmentEntryApiController::class, 'surveys']);
        Route::get('/survey-options', [AssessmentEntryApiController::class, 'surveyOptions']);
        Route::put('/surveys/{id}', [AssessmentEntryApiController::class, 'updateSurvey']);
        Route::delete('/surveys/{id}', [AssessmentEntryApiController::class, 'destroySurvey']);
    });

    // NBA SAR Criterion Scores
    Route::get(
        '/sar/criterion-scores',
        [CriterionScoreApiController::class, 'index']
    );

    // NBA Readiness
    Route::get(
        '/sar/nba-readiness',
        [\App\Http\Controllers\Api\V1\NbaReadinessApiController::class, 'index']
    );

    // Academic Audits CRUD
    Route::apiResource('academic-audits', AcademicAuditApiController::class);

    // 6. NBA SAR Engines & Matrices
    Route::prefix('nba')->group(function () {

        // Executive Dashboard & Overview
        Route::get('/dashboard', [NbaDashboardApiController::class, 'index']);

        // Criterion 5: Faculty Engines (SFR, FQI, Cadre, Table 5A)
        Route::get('/sfr-metrics', [SfrCalculatorApiController::class, 'getMetrics']);
        Route::get('/faculty-table-5a', [SfrCalculatorApiController::class, 'getTable5a']);
        Route::post('/sfr-simulate', [SfrCalculatorApiController::class, 'simulate']);
        Route::get('/fqi-metrics', [FqiCalculatorApiController::class, 'getMetrics']);
        Route::post('/fqi-simulate', [FqiCalculatorApiController::class, 'simulate']);

        // Criterion 2 & 3: Curriculum & Articulation Matrix
        Route::get('/course-matrix/{courseId}', [ArticulationMatrixApiController::class, 'getCourseMatrix']);
        Route::get('/program-matrix', [ArticulationMatrixApiController::class, 'getProgramMatrix']);
        Route::post('/update-mapping', [ArticulationMatrixApiController::class, 'updateMapping']);

        // Criterion 3: Attainment
        Route::get('/attainment/final', [AttainmentApiController::class, 'getFinalAttainment']);
        Route::get('/attainment/course/{courseId}', [AttainmentApiController::class, 'getCourseDirectAttainment']);
        Route::get('/attainment/course/{courseId}/performance', [AttainmentApiController::class, 'getCoursePerformance']);
        Route::get('/attainment/indirect', [AttainmentApiController::class, 'getIndirectAttainment']);
        Route::post('/attainment/record-marks', [AssessmentEntryApiController::class, 'recordMarks']);
        Route::post('/attainment/record-survey', [AssessmentEntryApiController::class, 'recordSurvey']);

        // SAR Workflow State Engine & Audit Logs
        Route::get('/workflow/{reportId}', [SarWorkflowApiController::class, 'getStatus']);
        Route::post('/workflow/{reportId}/transition', [SarWorkflowApiController::class, 'transition']);

        // SAR Evidence
        Route::get(
            '/workflow/{reportId}/criteria/{criterionId}/evidence',
            [\App\Http\Controllers\Api\V1\SarEvidenceApiController::class, 'index']
        );

        Route::post(
            '/workflow/{reportId}/criteria/{criterionId}/evidence',
            [\App\Http\Controllers\Api\V1\SarEvidenceApiController::class, 'upload']
        );

        Route::delete(
            '/evidence/{evidenceId}',
            [\App\Http\Controllers\Api\V1\SarEvidenceApiController::class, 'destroy']
        );

        Route::get(
            '/evidence/{evidenceId}/download',
            [\App\Http\Controllers\Api\V1\SarEvidenceApiController::class, 'download']
        );

        // Export Full SAR Report
        Route::get(
            '/export/{reportId}',
            [SarReportExportApiController::class, 'getFullDocument']
        );
    });
});