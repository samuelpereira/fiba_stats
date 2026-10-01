<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Closure;
use Database\Factories\SeasonFactory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property bool $is_current
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'slug', 'starts_at', 'ends_at', 'is_current'])]
class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Team, $this>
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Build the slug and the September-to-August period for a name such as 2026/2027.
     *
     * @return array{slug: string, starts_at: CarbonImmutable, ends_at: CarbonImmutable}|null
     */
    public static function attributesFromName(string $name): ?array
    {
        if (! preg_match('/^(\d{4})\/(\d{4})$/', $name, $matches)) {
            return null;
        }

        $startYear = (int) $matches[1];
        $endYear = (int) $matches[2];

        if ($endYear !== $startYear + 1) {
            return null;
        }

        $startsAt = CarbonImmutable::create($startYear, 9, 1);
        $endsAt = CarbonImmutable::create($endYear, 8, 31);

        if (! $startsAt instanceof CarbonImmutable || ! $endsAt instanceof CarbonImmutable) {
            return null;
        }

        return [
            'slug' => $startYear.'-'.$endYear,
            'starts_at' => $startsAt->startOfDay(),
            'ends_at' => $endsAt->startOfDay(),
        ];
    }

    /**
     * @return array<int, ValidationRule|Closure|string>
     */
    public static function nameRules(?int $ignoreId = null): array
    {
        return [
            'required',
            'string',
            'max:255',
            $ignoreId === null
                ? Rule::unique(self::class, 'name')
                : Rule::unique(self::class, 'name')->ignore($ignoreId),
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || self::attributesFromName($value) === null) {
                    $fail(__('The season name must be two consecutive years, such as 2026/2027.'));
                }
            },
        ];
    }
}
