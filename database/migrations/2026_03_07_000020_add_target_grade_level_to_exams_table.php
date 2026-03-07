<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->unsignedTinyInteger('target_grade_level')
                ->nullable()
                ->after('class_id');
            $table->index(['status', 'target_grade_level'], 'exams_status_target_grade_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_status_target_grade_idx');
            $table->dropColumn('target_grade_level');
        });
    }
};

