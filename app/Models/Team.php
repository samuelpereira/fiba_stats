<?php

namespace App\Models;

use App\Enums\Gender;
use App\Models\Concerns\HasPublicUuid;
use Carbon\CarbonImmutable;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $club_id
 * @property int $season_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $competition
 * @property string|null $age_group
 * @property Gender $gender
 * @property string|null $fpb_url
 * @property string|null $photo
 * @property int $order
 * @property bool $enabled
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read string|null $photo_url
 * @property-read Club $club
 * @property-read Season $season
 */
#[Fillable(['club_id', 'season_id', 'name', 'slug', 'description', 'competition', 'age_group', 'gender', 'fpb_url', 'photo', 'order', 'enabled'])]
class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory, HasPublicUuid;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'order' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return BelongsTo<Season, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * @return HasMany<Player, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    /**
     * @return HasMany<Game, $this>
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photo === null
            ? null
            : Storage::disk('public')->url($this->photo));
    }
}
