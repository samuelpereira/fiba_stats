<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
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
    use HasFactory;

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
