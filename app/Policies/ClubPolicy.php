<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function view(User $user, Club $club): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function update(User $user, Club $club): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function delete(User $user, Club $club): bool
    {
        return false;
    }
}
