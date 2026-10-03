<?php

namespace App\Actions;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class SyncUserAssignedTeams
{
    /**
     * @param  list<int|string>  $teamIds
     */
    public function handle(User $user, int $clubId, array $teamIds): void
    {
        $normalizedIds = collect($teamIds)
            ->map(fn (int|string $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($normalizedIds === []) {
            $user->teams()->sync([]);

            return;
        }

        $allowedIds = Team::query()
            ->where('club_id', $clubId)
            ->whereIn('id', $normalizedIds)
            ->pluck('id')
            ->all();

        if (count($allowedIds) !== count($normalizedIds)) {
            throw new AuthorizationException;
        }

        $user->teams()->sync($allowedIds);
    }
}
