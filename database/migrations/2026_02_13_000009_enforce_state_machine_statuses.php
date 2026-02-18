<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // the raw ALTER syntax used here is MySQL-specific and will fail on sqlite during tests
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE exams MODIFY status ENUM('draft', 'published', 'closed', 'running', 'finished') NOT NULL DEFAULT 'draft'");
            DB::statement("UPDATE exams SET status = 'running' WHERE status = 'published'");
            DB::statement("UPDATE exams SET status = 'finished' WHERE status = 'closed'");
            DB::statement("ALTER TABLE exams MODIFY status ENUM('draft', 'running', 'finished') NOT NULL DEFAULT 'draft'");

            DB::statement("ALTER TABLE exam_attempts MODIFY status ENUM('in_progress', 'submitted', 'scored', 'active', 'frozen', 'finished') NOT NULL DEFAULT 'in_progress'");
            DB::statement("UPDATE exam_attempts SET status = 'active' WHERE status = 'in_progress'");
            DB::statement("UPDATE exam_attempts SET status = 'finished' WHERE status = 'scored'");
            DB::statement("UPDATE exam_attempts SET status = 'finished' WHERE status = 'frozen'");
            DB::statement("ALTER TABLE exam_attempts MODIFY status ENUM('active', 'submitted', 'finished') NOT NULL DEFAULT 'active'");

            DB::statement("ALTER TABLE match_questions ADD COLUMN status ENUM('idle', 'active', 'locked', 'scored') NOT NULL DEFAULT 'idle' AFTER duration");
            DB::statement("ALTER TABLE match_questions ADD COLUMN winner_team_id BIGINT UNSIGNED NULL AFTER status");
            DB::statement("ALTER TABLE match_questions ADD COLUMN locked_at TIMESTAMP NULL AFTER ended_at");
            DB::statement("ALTER TABLE match_questions ADD COLUMN scored_at TIMESTAMP NULL AFTER locked_at");
            DB::statement("ALTER TABLE match_questions ADD COLUMN finished_at TIMESTAMP NULL AFTER scored_at");
            DB::statement("ALTER TABLE match_questions ADD CONSTRAINT match_questions_winner_team_id_foreign FOREIGN KEY (winner_team_id) REFERENCES teams(id) ON DELETE SET NULL");
            DB::statement("UPDATE match_questions SET status = CASE WHEN scored_at IS NOT NULL OR finished_at IS NOT NULL THEN 'scored' WHEN winner_team_id IS NOT NULL OR locked_at IS NOT NULL THEN 'locked' WHEN started_at IS NOT NULL AND ended_at > NOW() THEN 'active' ELSE 'idle' END");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE exams SET status = 'published' WHERE status = 'running'");
            DB::statement("UPDATE exams SET status = 'closed' WHERE status = 'finished'");
            DB::statement("ALTER TABLE exams MODIFY status ENUM('draft', 'published', 'closed') NOT NULL DEFAULT 'draft'");

            DB::statement("UPDATE exam_attempts SET status = 'in_progress' WHERE status = 'active'");
            DB::statement("UPDATE exam_attempts SET status = 'scored' WHERE status = 'finished'");
            DB::statement("ALTER TABLE exam_attempts MODIFY status ENUM('in_progress', 'submitted', 'scored') NOT NULL DEFAULT 'in_progress'");

            DB::statement("ALTER TABLE match_questions DROP FOREIGN KEY match_questions_winner_team_id_foreign");
            DB::statement("ALTER TABLE match_questions DROP COLUMN finished_at");
            DB::statement("ALTER TABLE match_questions DROP COLUMN scored_at");
            DB::statement("ALTER TABLE match_questions DROP COLUMN locked_at");
            DB::statement("ALTER TABLE match_questions DROP COLUMN winner_team_id");
            DB::statement("ALTER TABLE match_questions DROP COLUMN status");
        }
    }
};
