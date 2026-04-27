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
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('student_profile_id')->references('id')->on('student_profiles')->onDelete('cascade');
            $table->foreignId('teacher_profile_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreignId('town_id')->references('id')->on('towns')->onDelete('cascade');
            $table->foreignId('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->dateTime('slot_starts_at');
            $table->dateTime('slot_ends_at');
            $table->string('status',20)->default('booked');
            $table->string('payment_status',20);
            $table->decimal('price',10,2)->nullable();
            $table->string('booking_reference',40)->unique();
            $table->string('student_comments',255)->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->index(['teacher_profile_id', 'session_date','status']);
            $table->index(['student_profile_id', 'session_date','status']);
            $table->index(['town_id', 'session_date','status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
