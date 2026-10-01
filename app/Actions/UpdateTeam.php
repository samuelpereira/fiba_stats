<?php

namespace App\Actions;

use App\Actions\Concerns\StoresPublicPhoto;
use App\Enums\Gender;
use App\Enums\PermissionName;
use App\Models\Club;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class UpdateTeam
{
    use StoresPublicPhoto;

    /**
     * Update a team. A club role cannot move it to another club.
     */
    public function handle(
        User $actor,
        Team $team,
        string $name,
        int $seasonId,
        Gender $gender,
        ?int $clubId = null,
        ?string $description = null,
        ?string $competition = null,
        ?string $ageGroup = null,
        ?string $fpbUrl = null,
        int $order = 0,
        bool $enabled = true,
        ?UploadedFile $photo = null,
    ): Team {
        if (! $actor->can(PermissionName::CreateUsers->value)) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            if ($team->club_id !== $actor->club_id) {
                throw new AuthorizationException;
            }

            $clubId = $actor->club_id;
        }

        $club = Club::query()->find($clubId);
        $season = Season::query()->find($seasonId);

        if (! $club instanceof Club || ! $season instanceof Season) {
            throw new AuthorizationException;
        }

        $slug = Team::slugFromName($name);

        if ($slug === '') {
            throw new InvalidArgumentException('The team name is invalid.');
        }

        $team->club_id = $club->id;
        $team->season_id = $season->id;
        $team->name = $name;
        $team->slug = $slug;
        $team->description = $this->blankToNull($description);
        $team->competition = $this->blankToNull($competition);
        $team->age_group = $this->blankToNull($ageGroup);
        $team->gender = $gender;
        $team->fpb_url = $this->blankToNull($fpbUrl);
        $team->order = $order;
        $team->enabled = $enabled;
        $team->photo = $this->storePhoto($photo, $team->photo, 'teams');
        $team->save();

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
