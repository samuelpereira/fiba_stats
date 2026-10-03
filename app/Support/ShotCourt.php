<?php

namespace App\Support;

use App\Enums\StatType;

class ShotCourt
{
    public const int Width = 1000;

    public const int Height = 872;

    /** Baseline with the basket (top of the diagram). */
    public const int BaselineY = 60;

    /** Half-court line (bottom of the diagram). */
    public const int HalfCourtY = 812;

    public const int HoopX = 500;

    public const int CourtLeft = 100;

    public const int CourtRight = 900;

    private const int MarginX = 100;

    private const int MarginY = 60;

    private const float FeetWide = 50;

    private const float FeetLong = 47;

    /** Same scale on both axes, matching the reference chart. */
    private const float PixelsPerFoot = 16;

    /** Measured on the reference chart. */
    private const float ThreePointFeet = 22.59;

    private const float CornerThreeFeet = 3.0;

    private const float HoopFromBaselineFeet = 5.39;

    public static function pixels(float $feet): int
    {
        return (int) round($feet * self::PixelsPerFoot);
    }

    public static function hoopY(): int
    {
        return self::BaselineY + self::pixels(self::HoopFromBaselineFeet);
    }

    public static function threePointRadius(): int
    {
        return self::pixels(self::ThreePointFeet);
    }

    public static function cornerX(): int
    {
        return self::CourtLeft + self::pixels(self::CornerThreeFeet);
    }

    public static function threePointJoinY(): int
    {
        $radius = self::threePointRadius();
        $offset = self::HoopX - self::cornerX();

        return (int) round(self::hoopY() + sqrt($radius * $radius - $offset * $offset));
    }

    public static function threePointLinePath(): string
    {
        $left = self::cornerX();
        $right = self::Width - $left;
        $radius = self::threePointRadius();
        $join = self::threePointJoinY();

        return "M {$left} ".self::BaselineY
            ." L {$left} {$join}"
            ." A {$radius} {$radius} 0 0 0 {$right} {$join}"
            ." L {$right} ".self::BaselineY;
    }

    /**
     * Map a half-court position in feet onto the drawn court.
     * The source origin is the left baseline corner; y increases toward half court.
     *
     * @return array{x: int, y: int}
     */
    public static function fromFeet(float $x, float $y): array
    {
        return [
            'x' => (int) round(self::MarginX + ($x / self::FeetWide) * self::pixels(self::FeetWide)),
            'y' => (int) round(self::MarginY + ($y / self::FeetLong) * self::pixels(self::FeetLong)),
        ];
    }

    public static function allows(StatType $type, int $courtX, int $courtY): bool
    {
        if (! $type->isFieldGoal()) {
            return false;
        }

        if (! self::inBounds($courtX, $courtY)) {
            return false;
        }

        return match ($type) {
            StatType::TwoPointMade, StatType::TwoPointMissed => true,
            StatType::ThreePointMade, StatType::ThreePointMissed => ! self::isInsideThreePointLine($courtX, $courtY),
            default => false,
        };
    }

    public static function inBounds(int $courtX, int $courtY): bool
    {
        return $courtX >= self::MarginX
            && $courtX <= self::Width - self::MarginX
            && $courtY >= self::MarginY
            && $courtY <= self::Height - self::MarginY;
    }

    private static function isInsideThreePointLine(int $courtX, int $courtY): bool
    {
        $corner = self::cornerX();

        if ($courtX < $corner || $courtX > self::Width - $corner) {
            return false;
        }

        return self::distanceFromHoop($courtX, $courtY) <= self::threePointRadius();
    }

    private static function distanceFromHoop(int $courtX, int $courtY): float
    {
        return hypot($courtX - self::HoopX, $courtY - self::hoopY());
    }
}
