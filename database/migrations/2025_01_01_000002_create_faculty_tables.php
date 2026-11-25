<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculty_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('faculty_id')->unique();
            $table->string('name');
            $table->string('pan')->nullable();
            $table->string('apaar_id')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->date('joining_date');
            $table->string('highest_degree'); // Ph.D., M.Tech, M.E., M.Sc., B.Tech
            $table->string('degree_specialization')->nullable();
            $table->string('university_awarded')->nullable();
            $table->integer('year_of_highest_degree')->nullable();
            $table->integer('teaching_experience_years')->default(0);
            $table->integer('industry_experience_years')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('faculty_academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation'); // Professor, Associate Professor, Assistant Professor, Adjunct
            $table->string('cadre'); // Professor, Associate Professor, Assistant Professor
            $table->string('pay_scale')->nullable(); // e.g. 7th CPC Level 14, Level 13A, Level 10
            $table->string('association_type')->default('Regular'); // Regular, Contractual, Adjunct
            $table->boolean('is_exclusive_to_program')->default(true);
            $table->timestamps();
        });

        Schema::create('faculty_fdps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->integer('duration_days')->default(5);
            $table->string('organizer');
            $table->string('venue')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('faculty_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('journal_conference_name');
            $table->string('publication_type')->default('Journal'); // Journal, Conference, Book Chapter, Patent
            $table->string('indexing')->nullable(); // SCI, Scopus, Web of Science, UGC-CARE
            $table->decimal('impact_factor', 4, 2)->nullable();
            $table->date('published_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_publications');
        Schema::dropIfExists('faculty_fdps');
        Schema::dropIfExists('faculty_academic_years');
        Schema::dropIfExists('faculty_profiles');
    }
};
