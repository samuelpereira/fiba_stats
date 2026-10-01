<?php

namespace App\Actions;

use App\Actions\Concerns\StoresPublicPhoto;
use App\Enums\PermissionName;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class UpdatePlayer
{
    use StoresPublicPhoto;

    /**
     * Update a player. A club role cannot move the player to another club's team.
     */
    public function handle(
        User $actor,
        Player $player,
        string $name,
        int $teamId,
        ?string $fpbId = null,
        ?CarbonImmutable $dateOfBirth = null,
        ?int $number = null,
        ?string $position = null,
        int $order = 0,
        bool $enabled = true,
        ?UploadedFile $photo = null,
    ): Player {
        $player->loadMissing('team');

        if (! $actor->can(PermissionName::CreateUsers->value)) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateClubs->value) && $player->team->club_id !== $actor->club_id) {
            throw new AuthorizationException;
        }

        $team = Team::query()->find($teamId);

        if (! $team instanceof Team) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateClubs->value) && $team->club_id !== $actor->club_id) {
            throw new AuthorizationException;
        }

        $slug = Player::slugFromName($name);

        if ($slug === '') {
            throw new InvalidArgumentException('The player name is invalid.');
        }

        $player->team_id = $team->id;
        $player->fpb_id = $this->blankToNull($fpbId);
        $player->name = $name;
        $player->slug = $slug;
        $player->date_of_birth = $dateOfBirth;
        $player->number = $number;
        $player->position = $this->blankToNull($position);
        $player->order = $order;
        $player->enabled = $enabled;
        $player->photo = $this->storePhoto($photo, $player->photo, 'players');
        $player->save();

        return $player;
    }

    private function blankToNull(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
