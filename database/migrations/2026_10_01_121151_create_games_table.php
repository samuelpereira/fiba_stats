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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->restrictOnDelete();
            $table->string('fpb_id')->nullable()->unique();
            $table->string('opponent');
            $table->dateTime('date_time');
            $table->string('place')->nullable();
            $table->string('home_away');
            $table->string('competition')->nullable();
            $table->string('journey')->nullable();
            $table->unsignedSmallInteger('home_points')->nullable();
            $table->unsignedSmallInteger('away_points')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
