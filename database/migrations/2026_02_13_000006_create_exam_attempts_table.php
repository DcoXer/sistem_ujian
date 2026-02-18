<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'submitted', 'scored'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('answers_locked_at')->nullable();
            $table->timestamp('scoring_processed_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
