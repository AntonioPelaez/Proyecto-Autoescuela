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
        Schema::table('student_skills_evaluations', function (Blueprint $table) {
            $table->dropColumn('ready_for_exam');
            $table->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_skills_evaluations', function (Blueprint $table) {
             $table->boolean('ready_for_exam')->default(false);
             $table->text('notes')->nullable();
        });
    }
};
