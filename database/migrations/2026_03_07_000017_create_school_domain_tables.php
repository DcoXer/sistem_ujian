<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->unsignedTinyInteger('grade_level')->nullable();
            $table->string('major', 64)->nullable();
            $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_year_id', 'grade_level'], 'school_classes_year_grade_idx');
            $table->unique(['school_year_id', 'name'], 'school_classes_year_name_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_years');
    }
};
