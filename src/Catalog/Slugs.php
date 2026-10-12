<?php

declare(strict_types=1);

namespace App\Catalog;

final class Slugs
{
    public static function from(string $value): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));

        return '' !== $slug ? $slug : 'item';
    }
}
