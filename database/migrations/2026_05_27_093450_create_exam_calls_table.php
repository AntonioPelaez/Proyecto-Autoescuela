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
        Schema::create('exam_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('town_id')->constrained('towns')->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->foreignId('exam_call_status_id')->constrained('exam_call_status')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_calls');
    }
};
