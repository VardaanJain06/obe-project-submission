<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('faculty_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('faculty_profiles', 'pan')) {
                $table->dropColumn('pan');
            }
            if (!Schema::hasColumn('faculty_profiles', 'designation_at_joining')) {
                $table->string('designation_at_joining')->nullable();
            }
            if (!Schema::hasColumn('faculty_profiles', 'present_designation')) {
                $table->string('present_designation')->nullable();
            }
            if (!Schema::hasColumn('faculty_profiles', 'experience_display')) {
                $table->string('experience_display')->nullable();
            }
            if (!Schema::hasColumn('faculty_profiles', 'employment_type')) {
                $table->string('employment_type')->default('Full Time');
            }
            if (!Schema::hasColumn('faculty_profiles', 'currently_associated')) {
                $table->string('currently_associated')->default('Yes');
            }
            if (!Schema::hasColumn('faculty_profiles', 'date_of_leaving')) {
                $table->string('date_of_leaving')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('faculty_profiles', 'pan')) {
                $table->string('pan')->nullable();
            }
            $table->dropColumn([
                'designation_at_joining',
                'present_designation',
                'experience_display',
                'employment_type',
                'currently_associated',
                'date_of_leaving',
            ]);
        });
    }
};
