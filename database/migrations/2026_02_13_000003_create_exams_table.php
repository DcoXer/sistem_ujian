<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->unsignedInteger('duration_minutes');
            // include every possible state up front so sqlite tests don't trip on the
            // check constraint; the subsequent state machine migration will still
            // adjust values for MySQL but will be skipped during testing.
            $table->enum('status', ['draft', 'published', 'closed', 'running', 'finished'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
