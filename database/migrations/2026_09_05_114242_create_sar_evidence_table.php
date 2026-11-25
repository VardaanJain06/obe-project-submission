<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sar_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sar_criterion_id')->constrained('sar_criteria')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('uploaded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sar_evidences');
    }
};
