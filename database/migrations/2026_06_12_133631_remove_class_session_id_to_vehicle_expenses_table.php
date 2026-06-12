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
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            $table->dropForeign('vehicle_expenses_class_session_id_foreign');
            $table->dropColumn('class_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_expenses', function (Blueprint $table) {
            $table->foreignId('class_session_id')
                ->nullable()
                ->after('vehicle_id')
                ->constrained('class_sessions')
                ->cascadeOnDelete();
        });
    }
};
