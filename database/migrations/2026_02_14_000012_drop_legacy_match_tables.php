<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('answers');
        Schema::dropIfExists('buzzes');
        Schema::dropIfExists('match_questions');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('matches');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('status', ['waiting', 'running', 'finished'])->default('waiting');
            $table->foreignId('current_question_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('answer');
            $table->enum('type', ['rebutan', 'wajib', 'lemparan']);
            $table->integer('duration')->default(30);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->string('name');
            $table->integer('score')->default(0);
            $table->timestamps();
        });

        Schema::create('match_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->integer('order');
            $table->boolean('is_used')->default(false);
            $table->integer('duration');
            $table->enum('status', ['idle', 'active', 'locked', 'scored'])->default('idle');
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('buzzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->timestamp('buzzed_at');
            $table->timestamps();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('answer');
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }
};
