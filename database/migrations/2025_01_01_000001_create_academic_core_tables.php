<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('head_of_institution')->nullable();
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('hod_name')->nullable();
            $table->string('hod_email')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "2024-25"
            $table->string('type'); // CAY, CAYm1, CAYm2, CAYm3, CFY, CFYm1, CFYm2
            $table->integer('start_year');
            $table->integer('end_year');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. B.Tech Computer Science and Engineering
            $table->string('code')->unique(); // e.g. CSE-UG
            $table->string('degree_level')->default('UG'); // UG, PG
            $table->integer('sanctioned_intake')->default(60);
            $table->integer('year_of_start')->default(2010);
            $table->string('nba_tier')->default('Tier-II'); // Tier-I, Tier-II
            $table->string('coordinator_name')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->integer('ug_1st_year')->default(0); // 1st year students (UGT1)
            $table->integer('ug_2nd_year')->default(0); // 2nd year students (UGT2)
            $table->integer('ug_3rd_year')->default(0); // 3rd year students (UGT3)
            $table->integer('ug_4th_year')->default(0); // 4th year students (UGT4)
            $table->integer('lateral_entry')->default(0);
            $table->integer('total_students')->virtualAs('ug_1st_year + ug_2nd_year + ug_3rd_year + ug_4th_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_intakes');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('institutions');
    }
};
