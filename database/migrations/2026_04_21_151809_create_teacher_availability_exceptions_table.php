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
        Schema::create('teacher_availability_exceptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('teacher_profile_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreignId('town_id')->references('id')->on('towns')->onDelete('cascade')->nullable();
            $table->date('exception_date');
            $table->time('starts_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('type',20);
            $table->string('reason',150)->nullable();
            $table->index(['teacher_profile_id', 'exception_date'], 'idx_teacher_exception_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_availability_exceptions');
    }
};
