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
        Schema::create('match_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            $table->integer('order');
            $table->boolean('is_used')->default(false);

            $table->integer('duration'); // copy dari questions saat assign

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_questions');
    }
};
