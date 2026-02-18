<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->timestamp('authoring_start_at')->nullable()->after('end_at');
            $table->timestamp('authoring_end_at')->nullable()->after('authoring_start_at');
        });

        DB::table('exams')
            ->whereNull('authoring_start_at')
            ->update([
                'authoring_start_at' => DB::raw('start_at'),
                'authoring_end_at' => DB::raw('end_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['authoring_start_at', 'authoring_end_at']);
        });
    }
};

