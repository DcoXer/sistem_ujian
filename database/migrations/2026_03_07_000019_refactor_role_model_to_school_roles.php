<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'author')->update(['role' => 'teacher']);
        DB::table('users')->where('role', 'peserta')->update(['role' => 'student']);
        DB::table('users')->where('role', 'user')->update(['role' => 'student']);

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nis')) {
                $table->string('nis', 32)->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('nis')->constrained('school_classes')->nullOnDelete();
            }
            $table->string('role')->default('student')->change();
        });

        try {
            DB::statement('ALTER TABLE users ADD UNIQUE users_nis_uq (nis)');
        } catch (\Throwable) {
            // no-op
        }
        try {
            DB::statement('ALTER TABLE users ADD INDEX users_class_id_idx (class_id)');
        } catch (\Throwable) {
            // no-op
        }

        if (Schema::hasColumn('users', 'student_number') || Schema::hasColumn('users', 'nisn')) {
            DB::statement('UPDATE users SET nis = COALESCE(student_number, nisn) WHERE nis IS NULL');
        }
        if (Schema::hasColumn('users', 'school_class_id')) {
            DB::statement('UPDATE users SET class_id = school_class_id WHERE class_id IS NULL');
        }

        if (DB::getDriverName() !== 'sqlite') {
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_student_number_unique');
            } catch (\Throwable) {
                // no-op
            }
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_nisn_unique');
            } catch (\Throwable) {
                // no-op
            }
            try {
                DB::statement('ALTER TABLE users DROP FOREIGN KEY users_school_class_id_foreign');
            } catch (\Throwable) {
                // no-op
            }
            try {
                DB::statement('ALTER TABLE users DROP INDEX users_school_class_id_index');
            } catch (\Throwable) {
                // no-op
            }

            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'student_number')) {
                    $table->dropColumn('student_number');
                }
                if (Schema::hasColumn('users', 'nisn')) {
                    $table->dropColumn('nisn');
                }
            });
        }

        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('exams', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('school_class_id')->constrained('school_classes')->nullOnDelete();
            }
        });
        try {
            DB::statement('ALTER TABLE exams ADD INDEX exams_teacher_subject_class_idx (teacher_id, subject_id, class_id)');
        } catch (\Throwable) {
            // no-op
        }

        if (Schema::hasColumn('exams', 'author_id')) {
            DB::statement('UPDATE exams SET teacher_id = author_id WHERE teacher_id IS NULL');
        }
        if (Schema::hasColumn('exams', 'school_class_id')) {
            DB::statement('UPDATE exams SET class_id = school_class_id WHERE class_id IS NULL');
        }

        if (! Schema::hasTable('teacher_subjects')) {
            Schema::create('teacher_subjects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['teacher_id', 'subject_id', 'class_id'], 'teacher_subjects_unique_assignment');
                $table->index(['subject_id', 'class_id'], 'teacher_subjects_subject_class_idx');
            });
        }

        if (! Schema::hasTable('homeroom_teachers')) {
            Schema::create('homeroom_teachers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
                $table->timestamps();

                $table->unique('class_id', 'homeroom_teachers_class_unique');
                $table->unique(['teacher_id', 'class_id'], 'homeroom_teachers_teacher_class_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('homeroom_teachers');
        Schema::dropIfExists('teacher_subjects');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropIndex('exams_teacher_subject_class_idx');
            $table->dropConstrainedForeignId('class_id');
            $table->dropConstrainedForeignId('teacher_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('student_number', 32)->nullable()->after('role');
            $table->string('nisn', 32)->nullable()->after('student_number');
            $table->foreignId('school_class_id')->nullable()->after('nisn')->constrained('school_classes')->nullOnDelete();
            $table->index('school_class_id');
            $table->unique('student_number');
            $table->unique('nisn');
        });

        DB::statement('UPDATE users SET student_number = nis WHERE student_number IS NULL');
        DB::statement('UPDATE users SET school_class_id = class_id WHERE school_class_id IS NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nis_uq');
            $table->dropIndex('users_class_id_idx');
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn('nis');
        });

        DB::table('users')->where('role', 'teacher')->update(['role' => 'author']);
        DB::table('users')->where('role', 'student')->update(['role' => 'peserta']);
    }
};
