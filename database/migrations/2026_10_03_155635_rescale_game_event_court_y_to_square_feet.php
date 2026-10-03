<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('game_events')
            ->whereNotNull('court_y')
            ->update([
                'court_y' => DB::raw('CAST(ROUND(60 + (court_y - 60) * 752.0 / 820.0) AS INTEGER)'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('game_events')
            ->whereNotNull('court_y')
            ->update([
                'court_y' => DB::raw('CAST(ROUND(60 + (court_y - 60) * 820.0 / 752.0) AS INTEGER)'),
            ]);
    }
};
