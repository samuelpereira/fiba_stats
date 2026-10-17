<?php

namespace App\Models;

use Database\Factories\CollectCommandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $game_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Game $game
 */
#[Fillable(['id', 'game_id'])]
class CollectCommand extends Model
{
    /** @use HasFactory<CollectCommandFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
