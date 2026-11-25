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
            if (!Schema::hasColumn('faculty_profiles', 'pan')) {
                $table->string('pan')->nullable();
            }
            if (!Schema::hasColumn('faculty_profiles', 'date_designated_prof_assoc')) {
                $table->string('date_designated_prof_assoc')->nullable();
            }
            if (!Schema::hasColumn('faculty_profiles', 'contractual_type')) {
                $table->string('contractual_type')->nullable()->default('FT');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculty_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'pan',
                'date_designated_prof_assoc',
                'contractual_type',
            ]);
        });
    }
};
