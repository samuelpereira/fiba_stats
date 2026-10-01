<?php

namespace App\Actions;

use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteClub
{
    /**
     * Delete a club and its users when the club has no teams.
     */
    public function handle(Club $club): void
    {
        if ($club->teams()->exists()) {
            throw ValidationException::withMessages([
                'club' => __('This club still has teams.'),
            ]);
        }

        DB::transaction(function () use ($club): void {
            $club->users()->each(function (User $user): void {
                $user->delete();
            });

            $club->delete();
        });
    }
}
