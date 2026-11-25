<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_batch_id')
                ->constrained('assessment_batches')
                ->cascadeOnDelete();

            $table->foreignId('course_outcome_id')
                ->constrained('course_outcomes')
                ->cascadeOnDelete();

            $table->unsignedInteger('question_number');
            $table->decimal('max_marks', 8, 2);

            $table->timestamps();

            $table->unique([
                'assessment_batch_id',
                'question_number'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};