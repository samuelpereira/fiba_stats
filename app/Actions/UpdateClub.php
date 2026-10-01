<?php

namespace App\Actions;

use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class UpdateClub
{
    /**
     * Update a club and its existing access without creating another one.
     */
    public function handle(User $actor, Club $club, string $name, string $accessName, string $accessEmail, bool $enabled, ?string $password = null): Club
    {
        $access = $club->access()->sole();

        if ($actor->is($access) && ! $enabled) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($club, $access, $name, $accessName, $accessEmail, $enabled, $password): Club {
            $club->update([
                'name' => $name,
            ]);

            $access->name = $accessName;
            $access->email = $accessEmail;
            $access->enabled = $enabled;

            if (is_string($password) && $password !== '') {
                $access->password = $password;
            }

            $access->save();

            return $club;
        });
    }
}
