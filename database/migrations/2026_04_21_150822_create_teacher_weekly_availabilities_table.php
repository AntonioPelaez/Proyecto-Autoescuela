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
        Schema::create('teacher_weekly_availabilities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('teacher_profile_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreignId('town_id')->references('id')->on('towns')->onDelete('cascade');
            $table->tinyInteger('day_of_week'); // 0 (Sunday) to 6 (Saturday)
            $table->time('starts_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_minutes')->default(60); // Duration of each slot in minutes
            $table->boolean('is_active')->default(true);
            $table->index(['town_id', 'day_of_week', 'is_active'],'idx_town_day_active');
            $table->index(['teacher_profile_id', 'day_of_week', 'is_active'],'idx_teacher_day_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_weekly_availabilities');
    }
};
