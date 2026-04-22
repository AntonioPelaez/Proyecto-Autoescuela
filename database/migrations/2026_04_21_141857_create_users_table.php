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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->string('name',120);
            $table->string('surname1',120);
            $table->string('surname2',120)->nullable();
            $table->string('email',190)->unique();
            $table->string('phone',30)->nullable();
            $table->string('password')->hash();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken('remember_token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
