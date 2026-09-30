<?php

namespace App\Actions;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateManagedUser
{
    /**
     * Update a user-role account. A club role cannot move the user to another club.
     */
    public function handle(User $actor, User $user, string $name, string $email, ?string $password = null, ?int $clubId = null): User
    {
        if (! $user->hasRole(RoleName::User)) {
            throw new AuthorizationException;
        }

        if (! $actor->can(PermissionName::CreateUsers->value)) {
            throw new AuthorizationException;
        }

        $user->name = $name;
        $user->email = $email;

        if ($actor->can(PermissionName::CreateClubs->value)) {
            $club = Club::query()->find($clubId);

            if (! $club instanceof Club) {
                throw new AuthorizationException;
            }

            $user->club_id = $club->id;
        } elseif ($user->club_id !== $actor->club_id) {
            throw new AuthorizationException;
        }

        if (is_string($password) && $password !== '') {
            $user->password = $password;
        }

        $user->save();

        return $user;
    }
}
