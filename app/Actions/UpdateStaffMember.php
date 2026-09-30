<?php

namespace App\Actions;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateStaffMember
{
    /**
     * Update an administrator or a manager. An administrator cannot remove their own administrator role.
     */
    public function handle(User $actor, User $user, string $name, string $email, RoleName $role, ?string $password = null): User
    {
        $currentRole = $user->assignedRole();

        if (! $actor->hasRole(RoleName::Administrator) || ! in_array($currentRole, RoleName::staff(), true) || ! in_array($role, RoleName::staff(), true)) {
            throw new AuthorizationException;
        }

        if ($actor->is($user) && $role !== RoleName::Administrator) {
            throw new AuthorizationException;
        }

        $user->name = $name;
        $user->email = $email;
        $user->club_id = null;

        if (is_string($password) && $password !== '') {
            $user->password = $password;
        }

        $user->save();

        if ($currentRole !== $role) {
            $user->syncRoles([$role]);
        }

        return $user;
    }
}
