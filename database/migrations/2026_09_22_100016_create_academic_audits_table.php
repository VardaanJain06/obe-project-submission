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
        Schema::create('academic_audits', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->enum('audit_type', ['internal', 'external'])
                ->default('internal');

            $table->enum('status', ['pending', 'completed'])
                ->default('pending');

            $table->date('audit_date');

            $table->text('findings')->nullable();

            $table->text('recommendations')->nullable();

            $table->string('auditor_name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_audits');
    }
};