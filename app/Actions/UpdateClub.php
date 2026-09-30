<?php

namespace App\Actions;

use App\Models\Club;
use Illuminate\Support\Facades\DB;

class UpdateClub
{
    /**
     * Update a club and its existing access without creating another one.
     */
    public function handle(Club $club, string $name, string $accessName, string $accessEmail, ?string $password = null): Club
    {
        $access = $club->access()->sole();

        return DB::transaction(function () use ($club, $access, $name, $accessName, $accessEmail, $password): Club {
            $club->update([
                'name' => $name,
            ]);

            $access->name = $accessName;
            $access->email = $accessEmail;

            if (is_string($password) && $password !== '') {
                $access->password = $password;
            }

            $access->save();

            return $club;
        });
    }
}
