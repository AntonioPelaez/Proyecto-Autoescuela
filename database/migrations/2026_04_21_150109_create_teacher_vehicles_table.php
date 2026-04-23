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
        Schema::create('teacher_vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('teacher_profile_id')->references('id')->on('teacher_profiles')->onDelete('cascade');
            $table->foreignId('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_primary')->default(true); // Indica si el profesor utiliza principalmente este vehículo
            $table->index(['teacher_profile_id', 'is_primary']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_vehicles');
    }
};
