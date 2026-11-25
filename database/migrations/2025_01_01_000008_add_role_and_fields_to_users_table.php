<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Faculty')->after('email'); // Super Admin, HOD, Faculty, IQAC Admin, Evaluator
            $table->foreignId('department_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->string('designation')->nullable()->after('department_id');
            $table->string('faculty_code')->nullable()->after('designation');
            $table->string('phone')->nullable()->after('faculty_code');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['role', 'department_id', 'designation', 'faculty_code', 'phone', 'is_active']);
        });
    }
};
