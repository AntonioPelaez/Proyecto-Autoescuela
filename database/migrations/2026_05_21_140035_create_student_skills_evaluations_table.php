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
        Schema::create('student_skills_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_skill_id')->constrained('student_skill_evaluations')->onDelete('cascade');
            $table->foreignId('driving_skill_id')->constrained('driving_skills')->onDelete('cascade');
            $table->integer('score')->unsigned();
            $table->boolean('ready_for_exam')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_skills_evaluations');
    }
};
