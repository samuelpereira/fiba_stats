<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait InteractsWithAuthenticatedUser
{
    protected function actor(): User
    {
        $actor = Auth::user();

        abort_unless($actor instanceof User, 403);

        return $actor;
    }
}
