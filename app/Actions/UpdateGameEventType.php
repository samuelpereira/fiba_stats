<?php

namespace App\Actions;

use App\Enums\StatType;
use App\Models\Game;
use App\Models\GameEvent;
use App\Models\Player;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateGameEventType
{
    public function handle(Game $game, int $eventId, StatType $type, ?int $playerId): void
    {
        if (! $type->isEditableAction()) {
            throw (new ModelNotFoundException)->setModel(GameEvent::class, [$eventId]);
        }

        $event = $game->events()->whereKey($eventId)->first();

        if (! $event instanceof GameEvent || ! $event->type->isEditableAction()) {
            throw (new ModelNotFoundException)->setModel(GameEvent::class, [$eventId]);
        }

        if ($playerId === null) {
            $event->update([
                'type' => $type,
                'player_id' => null,
                'home_away' => $game->sideFor(true),
            ]);

            return;
        }

        $player = $game->players()->whereKey($playerId)->first();

        if (! $player instanceof Player) {
            throw (new ModelNotFoundException)->setModel(Player::class, [$playerId]);
        }

        $event->update([
            'type' => $type,
            'player_id' => $player->id,
            'home_away' => $game->home_away,
        ]);
    }
}
