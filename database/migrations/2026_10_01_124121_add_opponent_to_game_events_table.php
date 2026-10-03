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
        Schema::table('game_events', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
        });

        Schema::table('game_events', function (Blueprint $table) {
            $table->unsignedBigInteger('player_id')->nullable()->change();
            $table->boolean('for_opponent')->default(false)->after('player_id');
            $table->foreign('player_id')->references('id')->on('players')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->dropForeign(['player_id']);
            $table->dropColumn('for_opponent');
        });

        Schema::table('game_events', function (Blueprint $table) {
            $table->unsignedBigInteger('player_id')->nullable(false)->change();
            $table->foreign('player_id')->references('id')->on('players')->restrictOnDelete();
        });
    }
};
