<?php

namespace App\Enums;

enum HomeAway: string
{
    case Home = 'home';
    case Away = 'away';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'At home',
            self::Away => 'Away',
        };
    }

    public function opposite(): self
    {
        return match ($this) {
            self::Home => self::Away,
            self::Away => self::Home,
        };
    }
}
