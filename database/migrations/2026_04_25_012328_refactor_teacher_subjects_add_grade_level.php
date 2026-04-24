<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        $indexes = $this->getIndexNames();

        if ($driver === 'mysql') {
            // MySQL: FK constraints require a supporting index on each FK column.
            // The composite unique index is currently the supporting index for teacher_id and subject_id FKs.
            // Add dedicated single-col indexes first so the FKs don't rely on the composites we're dropping.
            if (! in_array('teacher_subjects_teacher_id_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects ADD INDEX teacher_subjects_teacher_id_idx (teacher_id)');
            }
            if (! in_array('teacher_subjects_subject_id_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects ADD INDEX teacher_subjects_subject_id_idx (subject_id)');
            }
            if (in_array('teacher_subjects_unique_assignment', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects DROP INDEX teacher_subjects_unique_assignment');
            }
            if (in_array('teacher_subjects_subject_class_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects DROP INDEX teacher_subjects_subject_class_idx');
            }
        } else {
            // SQLite / other: no FK-index dependency, use Schema builder.
            Schema::table('teacher_subjects', function (Blueprint $table) use ($indexes) {
                if (in_array('teacher_subjects_unique_assignment', $indexes, true)) {
                    $table->dropIndex('teacher_subjects_unique_assignment');
                }
                if (in_array('teacher_subjects_subject_class_idx', $indexes, true)) {
                    $table->dropIndex('teacher_subjects_subject_class_idx');
                }
            });
        }

        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->change();

            if (! Schema::hasColumn('teacher_subjects', 'grade_level')) {
                $table->unsignedTinyInteger('grade_level')->nullable()->after('class_id');
            }
        });

        $indexes = $this->getIndexNames();
        if (! in_array('teacher_subjects_subject_scope_idx', $indexes, true)) {
            Schema::table('teacher_subjects', function (Blueprint $table) {
                $table->index(['subject_id', 'class_id', 'grade_level'], 'teacher_subjects_subject_scope_idx');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        $indexes = $this->getIndexNames();

        if (in_array('teacher_subjects_subject_scope_idx', $indexes, true)) {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE teacher_subjects DROP INDEX teacher_subjects_subject_scope_idx');
            } else {
                Schema::table('teacher_subjects', function (Blueprint $table) {
                    $table->dropIndex('teacher_subjects_subject_scope_idx');
                });
            }
        }

        Schema::table('teacher_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_subjects', 'grade_level')) {
                $table->dropColumn('grade_level');
            }
            $table->foreignId('class_id')->nullable(false)->change();
        });

        $indexes = $this->getIndexNames();
        if ($driver === 'mysql') {
            if (! in_array('teacher_subjects_unique_assignment', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects ADD UNIQUE teacher_subjects_unique_assignment (teacher_id, subject_id, class_id)');
            }
            if (! in_array('teacher_subjects_subject_class_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects ADD INDEX teacher_subjects_subject_class_idx (subject_id, class_id)');
            }
            if (in_array('teacher_subjects_teacher_id_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects DROP INDEX teacher_subjects_teacher_id_idx');
            }
            if (in_array('teacher_subjects_subject_id_idx', $indexes, true)) {
                DB::statement('ALTER TABLE teacher_subjects DROP INDEX teacher_subjects_subject_id_idx');
            }
        } else {
            Schema::table('teacher_subjects', function (Blueprint $table) use ($indexes) {
                if (! in_array('teacher_subjects_unique_assignment', $indexes, true)) {
                    $table->unique(['teacher_id', 'subject_id', 'class_id'], 'teacher_subjects_unique_assignment');
                }
                if (! in_array('teacher_subjects_subject_class_idx', $indexes, true)) {
                    $table->index(['subject_id', 'class_id'], 'teacher_subjects_subject_class_idx');
                }
            });
        }
    }

    /** @return string[] */
    private function getIndexNames(): array
    {
        return array_column(Schema::getIndexes('teacher_subjects'), 'name');
    }
};
