<?php

namespace App\Models;

use App\Enums\GameStatus;
use App\Enums\HomeAway;
use Carbon\CarbonImmutable;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $team_id
 * @property string|null $fpb_id
 * @property string $opponent
 * @property CarbonImmutable $date_time
 * @property string|null $place
 * @property HomeAway $home_away
 * @property string|null $competition
 * @property string|null $journey
 * @property int|null $home_points
 * @property int|null $away_points
 * @property GameStatus $status
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Team $team
 */
#[Fillable(['team_id', 'fpb_id', 'opponent', 'date_time', 'place', 'home_away', 'competition', 'journey', 'home_points', 'away_points', 'status'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_time' => 'datetime',
            'home_away' => HomeAway::class,
            'home_points' => 'integer',
            'away_points' => 'integer',
            'status' => GameStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function score(): ?string
    {
        if ($this->home_points === null || $this->away_points === null) {
            return null;
        }

        return $this->home_points.'–'.$this->away_points;
    }
}
