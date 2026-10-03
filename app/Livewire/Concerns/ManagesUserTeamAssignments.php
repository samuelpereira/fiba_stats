<?php

namespace App\Livewire\Concerns;

use App\Enums\PermissionName;
use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

trait ManagesUserTeamAssignments
{
    /** @var list<string> */
    public array $teamIds = [];

    public function updatedClubId(): void
    {
        $this->teamIds = [];
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function teamAssignmentRules(bool $requireAtLeastOneTeam = true): array
    {
        $teamIdsRules = ['array'];

        if ($requireAtLeastOneTeam) {
            $teamIdsRules[] = 'required';
            $teamIdsRules[] = 'min:1';
        }

        return [
            'teamIds' => $teamIdsRules,
            'teamIds.*' => ['integer', 'distinct'],
        ];
    }

    protected function clubIdForTeamAssignment(): ?int
    {
        $actor = $this->actor();

        if ($actor->can(PermissionName::CreateClubs->value)) {
            return $this->clubId !== '' ? (int) $this->clubId : null;
        }

        return $actor->club_id;
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function assignableTeams(): Collection
    {
        $clubId = $this->clubIdForTeamAssignment();

        if ($clubId === null) {
            return Team::query()->whereRaw('0 = 1')->get();
        }

        return Team::query()
            ->with('season')
            ->where('club_id', $clubId)
            ->orderByDesc('season_id')
            ->orderBy('name')
            ->get();
    }
}
