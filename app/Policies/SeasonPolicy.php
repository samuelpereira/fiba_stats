<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Season;
use App\Models\User;

class SeasonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function view(User $user, Season $season): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function update(User $user, Season $season): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }

    public function delete(User $user, Season $season): bool
    {
        return $user->can(PermissionName::CreateClubs->value);
    }
}
