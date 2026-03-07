<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->index(['status', 'start_at'], 'exams_status_start_at_idx');
            $table->index(['status', 'end_at'], 'exams_status_end_at_idx');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->index(['status', 'id'], 'exam_attempts_status_id_idx');
            $table->index(['exam_id', 'status'], 'exam_attempts_exam_status_idx');
            $table->index(['user_id', 'status', 'started_at'], 'exam_attempts_user_status_started_idx');
            $table->index(['user_id', 'status', 'submitted_at'], 'exam_attempts_user_status_submitted_idx');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex('exam_attempts_status_id_idx');
            $table->dropIndex('exam_attempts_exam_status_idx');
            $table->dropIndex('exam_attempts_user_status_started_idx');
            $table->dropIndex('exam_attempts_user_status_submitted_idx');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_status_start_at_idx');
            $table->dropIndex('exams_status_end_at_idx');
        });
    }
};
