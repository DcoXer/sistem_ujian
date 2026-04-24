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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('semester_id')
                ->nullable()
                ->after('school_year_id')
                ->constrained('semesters')
                ->nullOnDelete();

            $table->string('exam_type', 16)
                ->nullable()
                ->after('semester_id')
                ->comment('UH, UTS, UAS, PAT');

            $table->index(['semester_id', 'exam_type'], 'exams_semester_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_semester_type_idx');
            $table->dropConstrainedForeignId('semester_id');
            $table->dropColumn('exam_type');
        });
    }
};
