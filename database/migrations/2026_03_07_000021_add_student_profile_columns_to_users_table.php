<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'nisn')) {
                $table->string('nisn', 32)->nullable()->after('nis');
            }
            if (! Schema::hasColumn('users', 'nik')) {
                $table->string('nik', 32)->nullable()->after('nisn');
            }
            if (! Schema::hasColumn('users', 'birth_place')) {
                $table->string('birth_place', 128)->nullable()->after('nik');
            }
            if (! Schema::hasColumn('users', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('birth_place');
            }
            if (! Schema::hasColumn('users', 'guardian_name')) {
                $table->string('guardian_name', 255)->nullable()->after('birth_date');
            }
        });

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('nisn', 'users_nisn_uq');
            });
        } catch (\Throwable) {
            // no-op
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['class_id', 'nisn'], 'users_class_nisn_idx');
            });
        } catch (\Throwable) {
            // no-op
        }
    }

    public function down(): void
    {
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_class_nisn_idx');
            });
        } catch (\Throwable) {
            // no-op
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_nisn_uq');
            });
        } catch (\Throwable) {
            // no-op
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = ['guardian_name', 'birth_date', 'birth_place', 'nik', 'nisn'];
            $droppable = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('users', $column)));
            if (! empty($droppable)) {
                $table->dropColumn($droppable);
            }
        });
    }
};
