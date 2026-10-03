<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->string('home_away')->nullable()->after('for_opponent');
        });

        $events = DB::table('game_events')
            ->join('games', 'games.id', '=', 'game_events.game_id')
            ->select('game_events.id', 'game_events.type', 'game_events.for_opponent', 'games.home_away as game_side')
            ->orderBy('game_events.id')
            ->get();

        foreach ($events as $event) {
            if (in_array($event->type, ['quarter_started', 'quarter_ended'], true)) {
                continue;
            }

            $side = (bool) $event->for_opponent
                ? ($event->game_side === 'home' ? 'away' : 'home')
                : $event->game_side;

            DB::table('game_events')->where('id', $event->id)->update(['home_away' => $side]);
        }

        Schema::table('game_events', function (Blueprint $table) {
            $table->dropColumn('for_opponent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_events', function (Blueprint $table) {
            $table->boolean('for_opponent')->default(false)->after('player_id');
        });

        $events = DB::table('game_events')
            ->join('games', 'games.id', '=', 'game_events.game_id')
            ->select('game_events.id', 'game_events.home_away as event_side', 'games.home_away as game_side')
            ->orderBy('game_events.id')
            ->get();

        foreach ($events as $event) {
            DB::table('game_events')->where('id', $event->id)->update([
                'for_opponent' => $event->event_side !== null && $event->event_side !== $event->game_side,
            ]);
        }

        Schema::table('game_events', function (Blueprint $table) {
            $table->dropColumn('home_away');
        });
    }
};
