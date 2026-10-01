<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Player;
use App\Models\User;

class PlayerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::CreateUsers->value);
    }

    public function view(User $user, Player $player): bool
    {
        return $this->manages($user, $player);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateUsers->value);
    }

    public function update(User $user, Player $player): bool
    {
        return $this->manages($user, $player);
    }

    public function delete(User $user, Player $player): bool
    {
        return $this->manages($user, $player);
    }

    private function manages(User $actor, Player $player): bool
    {
        if (! $actor->can(PermissionName::CreateUsers->value)) {
            return false;
        }

        $player->loadMissing('team');

        if ($actor->can(PermissionName::CreateClubs->value)) {
            return true;
        }

        return $actor->hasRole(RoleName::Club) && $actor->club_id === $player->team->club_id;
    }
}
