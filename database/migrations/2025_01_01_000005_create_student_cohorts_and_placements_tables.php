<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('batch_name'); // e.g., 2020-2024 (CAYm3 graduation year)
            $table->integer('entry_year'); // 2020
            $table->integer('graduation_year'); // 2024
            $table->string('cohort_type')->default('CAYm1'); // CAY, CAYm1, CAYm2, CAYm3
            $table->integer('sanctioned_intake')->default(60);
            $table->integer('admitted_first_year')->default(60);
            $table->integer('lateral_admitted_second_year')->default(6);
            $table->integer('total_admitted')->virtualAs('admitted_first_year + lateral_admitted_second_year');
            $table->integer('graduated_without_backlog')->default(45);
            $table->integer('graduated_in_stipulated_time')->default(55);
            $table->timestamps();
        });

        Schema::create('student_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->integer('final_year_students')->default(66);
            $table->integer('placed_students')->default(48);
            $table->integer('higher_studies_students')->default(8);
            $table->integer('entrepreneurs_count')->default(2);
            $table->decimal('median_salary_lpa', 5, 2)->default(6.50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_placements');
        Schema::dropIfExists('student_cohorts');
    }
};
