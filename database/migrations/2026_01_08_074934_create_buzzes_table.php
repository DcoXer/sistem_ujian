<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buzzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id');
            $table->foreignId('question_id');
            $table->foreignId('team_id');
            $table->unique(['match_id', 'question_id']);
            $table->timestamp('buzzed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buzzes');
    }
};
