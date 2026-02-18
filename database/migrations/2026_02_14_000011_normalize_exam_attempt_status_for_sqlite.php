<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('status_new', 32)->default('active')->after('status');
        });

        DB::table('exam_attempts')
            ->where('status', 'in_progress')
            ->update(['status_new' => 'active']);

        DB::table('exam_attempts')
            ->where('status', 'submitted')
            ->update(['status_new' => 'submitted']);

        DB::table('exam_attempts')
            ->whereIn('status', ['scored', 'frozen', 'finished'])
            ->update(['status_new' => 'finished']);

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('status_old', 32)->default('in_progress')->after('status');
        });

        DB::table('exam_attempts')
            ->where('status', 'active')
            ->update(['status_old' => 'in_progress']);

        DB::table('exam_attempts')
            ->where('status', 'submitted')
            ->update(['status_old' => 'submitted']);

        DB::table('exam_attempts')
            ->where('status', 'finished')
            ->update(['status_old' => 'scored']);

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};
