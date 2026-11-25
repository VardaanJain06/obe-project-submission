<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_outcome_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('assessment_type'); // Internal_Test_1, Internal_Test_2, Assignment, End_Sem
            $table->integer('total_students_attempted')->default(60);
            $table->integer('students_meeting_target')->default(45);
            $table->decimal('percentage_achieved', 5, 2)->default(75.00);
            $table->decimal('attainment_level', 3, 2)->default(3.00); // 1, 2, or 3
            $table->timestamps();
        });

        Schema::create('indirect_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('survey_type'); // Graduate_Exit_Survey, Alumni_Survey, Employer_Survey
            $table->string('target_type'); // PO or PSO
            $table->unsignedBigInteger('target_id'); // po_id or pso_id
            $table->decimal('average_rating', 3, 2)->default(2.50); // Scale of 3
            $table->integer('total_respondents')->default(50);
            $table->timestamps();
        });

        Schema::create('attainment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_outcome_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('direct_cie_score', 3, 2)->default(0.00); // CIE attainment
            $table->decimal('direct_see_score', 3, 2)->default(0.00); // SEE attainment
            $table->decimal('direct_attainment', 3, 2)->default(0.00); // Combined Direct (e.g. 20% CIE + 80% SEE or 30/70)
            $table->decimal('indirect_attainment', 3, 2)->default(0.00);
            $table->decimal('final_score', 3, 2)->default(0.00); // 0.8 * Direct + 0.2 * Indirect
            $table->timestamps();

            $table->unique(['course_outcome_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attainment_results');
        Schema::dropIfExists('indirect_assessments');
        Schema::dropIfExists('student_assessments');
    }
};
