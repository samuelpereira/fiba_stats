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

class CreateTeam
{
    use StoresPublicPhoto;

    /**
     * Create a team in a club and a season. A club role cannot choose another club.
     */
    public function handle(
        User $actor,
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
        $club = $this->clubFor($actor, $clubId);
        $season = Season::query()->find($seasonId);

        if (! $season instanceof Season) {
            throw new AuthorizationException;
        }

        $slug = Team::slugFromName($name);

        if ($slug === '') {
            throw new InvalidArgumentException('The team name is invalid.');
        }

        $team = new Team;
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
        $team->photo = $this->storePhoto($photo, null, 'teams');
        $team->save();

        return $team;
    }

    private function clubFor(User $actor, ?int $clubId): Club
    {
        if (! $actor->can(PermissionName::CreateUsers->value)) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            $clubId = $actor->club_id;
        }

        $club = Club::query()->find($clubId);

        if (! $club instanceof Club) {
            throw new AuthorizationException;
        }

        return $club;
    }

    private function blankToNull(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
