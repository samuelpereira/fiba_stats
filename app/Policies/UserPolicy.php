<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CreateUsers->value);
    }

    public function view(User $user, User $model): bool
    {
        return $this->manages($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateUsers->value);
    }

    public function update(User $user, User $model): bool
    {
        return $this->manages($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return false;
    }

    public function viewAnyStaff(User $user): bool
    {
        return $user->hasRole(RoleName::Administrator);
    }

    public function createStaff(User $user): bool
    {
        return $user->hasRole(RoleName::Administrator);
    }

    public function updateStaff(User $user, User $model): bool
    {
        return $user->hasRole(RoleName::Administrator)
            && in_array($model->assignedRole(), RoleName::staff(), true);
    }

    /**
     * Club users only. A club role can only reach its own club.
     */
    private function manages(User $actor, User $subject): bool
    {
        if (! $actor->can(PermissionName::CreateUsers->value) || ! $subject->hasRole(RoleName::User) || $subject->club_id === null) {
            return false;
        }

        if ($actor->can(PermissionName::CreateClubs->value)) {
            return true;
        }

        return $actor->hasRole(RoleName::Club) && $actor->club_id === $subject->club_id;
    }
}
