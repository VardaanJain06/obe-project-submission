<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sar_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete(); // Reference CAY
            $table->string('title'); // e.g. "SAR for B.Tech Computer Science & Engineering 2024-2025"
            $table->string('current_state')->default('DRAFT'); // DRAFT, DEPARTMENT_CONSOLIDATION, INSTITUTIONAL_REVIEW, APPROVED_LOCKED, ARCHIVED
            $table->string('submitted_by')->nullable();
            $table->string('hod_verified_by')->nullable();
            $table->string('iqac_approved_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->decimal('total_claimed_marks', 6, 2)->default(0.00); // Out of 1000 for Tier-II / Tier-I
            $table->decimal('evaluated_marks', 6, 2)->nullable();
            $table->text('workflow_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sar_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sar_report_id')->constrained()->cascadeOnDelete();
            $table->integer('criterion_number'); // 1 to 10
            $table->string('title'); // e.g. Criterion 5: Faculty Information & Contributions
            $table->decimal('max_marks', 5, 2); // e.g. 200, 100, 150
            $table->decimal('claimed_marks', 5, 2)->default(0.00);
            $table->string('status')->default('IN_PROGRESS'); // IN_PROGRESS, COMPLETED, VERIFIED
            $table->text('summary_observations')->nullable();
            $table->json('metrics_snapshot')->nullable(); // Snapshot of calculated metrics (SFR, FQI, etc.)
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->unique(['sar_report_id', 'criterion_number']);
        });

        Schema::create('sar_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sar_report_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('user_name')->default('System');
            $table->string('user_role')->default('Faculty'); // Faculty, HOD, IQAC Admin, Evaluator, System
            $table->string('action'); // STATE_TRANSITION, METRIC_CALCULATED, MARKS_UPDATED, CO_PO_MODIFIED, RECORD_LOCKED
            $table->string('domain_module')->nullable(); // Faculty, Curriculum, Attainment, Student, Finance, SAR
            $table->json('payload')->nullable(); // Previous vs New state / diff
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sar_audit_logs');
        Schema::dropIfExists('sar_criteria');
        Schema::dropIfExists('sar_reports');
    }
};
