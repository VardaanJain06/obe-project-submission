<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('financial_year_name'); // e.g. CFY (2024-25), CFYm1 (2023-24), CFYm2 (2022-23)
            $table->string('cfy_type')->default('CFY'); // CFY, CFYm1, CFYm2
            $table->decimal('total_budget_allocated', 15, 2)->default(0.00);
            $table->decimal('total_actual_expenditure', 15, 2)->default(0.00);
            $table->decimal('utilization_percentage', 5, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_budget_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // Recurring, Non-Recurring
            $table->string('sub_category'); // Laboratory Equipment, Software & Subscriptions, R&D & Conferences, Maintenance, Faculty Salaries, Library & Journals
            $table->decimal('budget_allocated', 15, 2)->default(0.00);
            $table->decimal('actual_expenditure', 15, 2)->default(0.00);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('financial_budgets');
    }
};
