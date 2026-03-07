<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number', 32)->nullable()->after('role');
            $table->string('nisn', 32)->nullable()->after('student_number');
            $table->foreignId('school_class_id')->nullable()->after('nisn')->constrained('school_classes')->nullOnDelete();

            $table->unique('student_number');
            $table->unique('nisn');
            $table->index('school_class_id');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->after('author_id')->constrained('subjects')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->after('subject_id')->constrained('school_classes')->nullOnDelete();
            $table->foreignId('school_year_id')->nullable()->after('school_class_id')->constrained('school_years')->nullOnDelete();

            $table->index(['school_year_id', 'school_class_id', 'subject_id'], 'exams_school_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_school_scope_idx');
            $table->dropConstrainedForeignId('school_year_id');
            $table->dropConstrainedForeignId('school_class_id');
            $table->dropConstrainedForeignId('subject_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['student_number']);
            $table->dropUnique(['nisn']);
            $table->dropIndex(['school_class_id']);
            $table->dropConstrainedForeignId('school_class_id');
            $table->dropColumn(['student_number', 'nisn']);
        });
    }
};
