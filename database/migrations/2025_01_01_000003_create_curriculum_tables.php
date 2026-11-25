<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('course_code')->index(); // e.g. CS401
            $table->string('course_name'); // e.g. Database Management Systems
            $table->integer('semester'); // 1 to 8
            $table->decimal('credits', 3, 1)->default(3.0);
            $table->integer('lecture_hours')->default(3);
            $table->integer('tutorial_hours')->default(0);
            $table->integer('practical_hours')->default(2);
            $table->string('course_type')->default('Core'); // Core, Professional Elective, Open Elective, Basic Science
            $table->timestamps();
        });

        Schema::create('program_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('po_code'); // PO1 to PO12
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('program_specific_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('pso_code'); // PSO1 to PSO3
            $table->string('title');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('course_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('co_code'); // CO1 to CO6
            $table->text('description');
            $table->decimal('target_percentage', 5, 2)->default(60.00); // 60% students should score > 60%
            $table->decimal('target_level', 3, 2)->default(2.50); // Scale of 3
            $table->string('bloom_level')->default('Apply'); // Remember, Understand, Apply, Analyze, Evaluate, Create
            $table->timestamps();
        });

        Schema::create('co_po_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_outcome_id')->constrained()->cascadeOnDelete();
            $table->string('target_type'); // 'PO' or 'PSO'
            $table->unsignedBigInteger('target_id'); // po_id or pso_id
            $table->unsignedTinyInteger('correlation_level')->default(2); // 1 = Slight/Low, 2 = Moderate/Medium, 3 = Substantial/High
            $table->text('justification')->nullable();
            $table->timestamps();

            $table->index(['course_outcome_id', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('co_po_mappings');
        Schema::dropIfExists('course_outcomes');
        Schema::dropIfExists('program_specific_outcomes');
        Schema::dropIfExists('program_outcomes');
        Schema::dropIfExists('courses');
    }
};
