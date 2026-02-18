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
            $table->foreignId('author_id')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();
        });

        $defaultAuthorId = DB::table('users')
            ->where('role', 'author')
            ->orderBy('id')
            ->value('id');

        if ($defaultAuthorId) {
            DB::table('exams')
                ->whereNull('author_id')
                ->update(['author_id' => $defaultAuthorId]);
        }
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
        });
    }
};
