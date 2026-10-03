<?php

namespace App\Actions;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CreateManagedUser
{
    /**
     * Create a user-role account. A club role can only create users in its own club.
     */
    /**
     * @param  list<int|string>  $teamIds
     */
    public function handle(User $actor, string $name, string $email, string $password, ?int $clubId = null, array $teamIds = []): User
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

        $user = $club->users()->make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->save();
        $user->markEmailAsVerified();
        $user->assignRole(RoleName::User);

        (new SyncUserAssignedTeams)->handle($user, $club->id, $teamIds);

        return $user;
    }
}
