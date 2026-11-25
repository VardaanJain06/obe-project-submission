<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_marks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assessment_question_id')
                ->constrained('assessment_questions')
                ->cascadeOnDelete();

            $table->string('student_identifier');
            $table->decimal('marks_obtained', 8, 2);

            $table->timestamps();

            $table->unique([
                'assessment_question_id',
                'student_identifier'
            ]);

            $table->index('student_identifier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_marks');
    }
};