<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use Carbon\CarbonImmutable;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property int $team_id
 * @property string|null $fpb_id
 * @property string $name
 * @property string $slug
 * @property CarbonImmutable|null $date_of_birth
 * @property int|null $number
 * @property string|null $position
 * @property string|null $photo
 * @property int $order
 * @property bool $enabled
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read string|null $photo_url
 * @property-read Team $team
 */
#[Fillable(['team_id', 'fpb_id', 'name', 'slug', 'date_of_birth', 'number', 'position', 'photo', 'order', 'enabled'])]
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory, HasPublicUuid;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'number' => 'integer',
            'order' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * @return HasMany<GameEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }

    public function shortName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $parts = array_values(array_filter(
            $parts,
            fn (string $part): bool => $part !== '',
        ));

        if (count($parts) < 2) {
            return $this->name;
        }

        $last = (string) array_pop($parts);

        return $parts[0].' '.mb_substr($last, 0, 1).'.';
    }

    /**
     * @return list<string>
     */
    public function nameLines(): array
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $parts = array_values(array_filter(
            $parts,
            fn (string $part): bool => $part !== '',
        ));

        if (count($parts) < 2) {
            return [$this->name];
        }

        $last = array_pop($parts);

        return [implode(' ', $parts), (string) $last];
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
