<?php

namespace App\Actions;

use App\Enums\RoleName;
use App\Models\Club;
use Illuminate\Support\Facades\DB;

class CreateClub
{
    /**
     * Create a club and the single club role that can create its users.
     */
    public function handle(string $name, string $accessName, string $accessEmail, string $password): Club
    {
        return DB::transaction(function () use ($name, $accessName, $accessEmail, $password): Club {
            $club = Club::query()->create([
                'name' => $name,
            ]);

            $access = $club->users()->make([
                'name' => $accessName,
                'email' => $accessEmail,
                'password' => $password,
            ]);
            $access->markEmailAsVerified();
            $access->assignRole(RoleName::Club);

            return $club;
        });
    }
}
