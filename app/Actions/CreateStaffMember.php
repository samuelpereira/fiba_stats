<?php

namespace App\Actions;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CreateStaffMember
{
    /**
     * Create an administrator or a manager. These accounts do not belong to a club.
     */
    public function handle(User $actor, string $name, string $email, string $password, RoleName $role): User
    {
        if (! $actor->hasRole(RoleName::Administrator) || ! in_array($role, RoleName::staff(), true)) {
            throw new AuthorizationException;
        }

        $user = User::query()->make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
        $user->markEmailAsVerified();
        $user->assignRole($role);

        return $user;
    }
}
