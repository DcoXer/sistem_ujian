<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('lock_version')->default(0)->after('locked_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_answers', function (Blueprint $table) {
            $table->dropColumn('lock_version');
        });
    }
};

