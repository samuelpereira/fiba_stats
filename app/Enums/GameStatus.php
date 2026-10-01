<?php

namespace App\Enums;

enum GameStatus: string
{
    case Scheduled = 'scheduled';
    case Played = 'played';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Played => 'Played',
            self::Postponed => 'Postponed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-zinc-100 text-zinc-700',
            self::Played => 'bg-green-100 text-green-700',
            self::Postponed => 'bg-amber-100 text-amber-800',
            self::Cancelled => 'bg-red-100 text-red-700',
        };
    }
}
