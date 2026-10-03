<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 */
trait HasPublicUuid
{
    use HasUuids;

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public static function findByPublicKey(int|string $key): static
    {
        $query = static::query();

        /** @var static $record */
        $record = $query
            ->where($query->getModel()->getRouteKeyName(), $key)
            ->firstOrFail();

        return $record;
    }
}
