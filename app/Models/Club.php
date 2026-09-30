<?php

namespace App\Models;

use App\Enums\RoleName;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name'])]
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * The club role allowed to create this club's users.
     *
     * @return HasOne<User, $this>
     */
    public function access(): HasOne
    {
        return $this->hasOne(User::class)->whereHas(
            'roles',
            fn (Builder $query) => $query->where('name', RoleName::Club->value),
        );
    }
}
