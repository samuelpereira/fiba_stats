<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DeleteUser
{
    /**
     * Delete an account other than the actor.
     */
    public function handle(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw new AuthorizationException;
        }

        $user->delete();
    }
}
