<?php

declare(strict_types=1);

namespace App\Service\Appointment;

final class KyivClock
{
    public const TIMEZONE = 'Europe/Kyiv';

    public static function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone(self::TIMEZONE));
    }

    public static function todayDateString(): string
    {
        return self::now()->format('Y-m-d');
    }

    public static function currentTimeString(): string
    {
        return self::now()->format('H:i');
    }
}
