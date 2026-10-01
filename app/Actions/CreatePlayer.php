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

class CreatePlayer
{
    use StoresPublicPhoto;

    /**
     * Create a player on a team the actor is allowed to manage.
     */
    public function handle(
        User $actor,
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
        $team = $this->teamFor($actor, $teamId);
        $slug = Player::slugFromName($name);

        if ($slug === '') {
            throw new InvalidArgumentException('The player name is invalid.');
        }

        $player = new Player;
        $player->team_id = $team->id;
        $player->fpb_id = $this->blankToNull($fpbId);
        $player->name = $name;
        $player->slug = $slug;
        $player->date_of_birth = $dateOfBirth;
        $player->number = $number;
        $player->position = $this->blankToNull($position);
        $player->order = $order;
        $player->enabled = $enabled;
        $player->photo = $this->storePhoto($photo, null, 'players');
        $player->save();

        return $player;
    }

    private function teamFor(User $actor, int $teamId): Team
    {
        if (! $actor->can(PermissionName::CreateUsers->value)) {
            throw new AuthorizationException;
        }

        $team = Team::query()->find($teamId);

        if (! $team instanceof Team) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateClubs->value) && $team->club_id !== $actor->club_id) {
            throw new AuthorizationException;
        }

        return $team;
    }

    private function blankToNull(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
