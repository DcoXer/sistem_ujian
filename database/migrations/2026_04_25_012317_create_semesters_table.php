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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->string('name', 32);
            $table->unsignedTinyInteger('semester_number');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['school_year_id', 'semester_number'], 'semesters_year_number_unique');
            $table->index('school_year_id', 'semesters_school_year_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
