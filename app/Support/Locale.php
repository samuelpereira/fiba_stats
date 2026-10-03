<?php

namespace App\Support;

class Locale
{
    /**
     * @return array<string, string>
     */
    public static function supported(): array
    {
        /** @var array<string, string> $supported */
        $supported = config('locales.supported', ['en' => 'English']);

        return $supported;
    }

    public static function isSupported(string $locale): bool
    {
        return array_key_exists($locale, self::supported());
    }
}
