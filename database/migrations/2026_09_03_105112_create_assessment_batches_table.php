<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();

            $table->string('assessment_type');
            $table->string('assessment_name');
            $table->unsignedInteger('total_marks');

            $table->timestamps();

            $table->unique([
                'course_id',
                'academic_year_id',
                'assessment_type',
                'assessment_name'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_batches');
    }
};