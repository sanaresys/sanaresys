<?php

namespace App\Support;

class CentralUrl
{
    public static function origin(): string
    {
        $appUrl = trim((string) config('app.url', ''));

        if ($appUrl !== '') {
            return rtrim($appUrl, '/');
        }

        $host = collect((array) config('tenancy.central_domains', []))
            ->map(static fn ($domain) => strtolower(trim((string) $domain)))
            ->first(static fn ($domain) => $domain !== '');

        $scheme = app()->environment('local') ? 'http' : 'https';

        return "{$scheme}://{$host}";
    }

    public static function route(string $name, array $parameters = []): string
    {
        return static::origin() . route($name, $parameters, false);
    }
}
