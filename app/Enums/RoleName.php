<?php

namespace App\Enums;

enum RoleName: string
{
    case Administrator = 'administrator';
    case Manager = 'manager';
    case Club = 'club';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::Manager => 'Manager',
            self::Club => 'Club',
            self::User => 'User',
        };
    }

    /**
     * Roles managed from the administrators menu.
     *
     * @return list<self>
     */
    public static function staff(): array
    {
        return [
            self::Administrator,
            self::Manager,
        ];
    }
}
