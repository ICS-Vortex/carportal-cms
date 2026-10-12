<?php

declare(strict_types=1);

namespace App\DataFixtures;

use Faker\Factory;
use Faker\Generator;

final class FixtureFaker
{
    private static ?Generator $uk = null;

    public static function uk(): Generator
    {
        if (null === self::$uk) {
            self::$uk = Factory::create('uk_UA');
            self::$uk->seed(20261012);
        }

        return self::$uk;
    }

    public static function vin(): string
    {
        return self::uk()->regexify('[A-HJ-NPR-Z0-9]{17}');
    }

    public static function licensePlateUa(): string
    {
        return self::uk()->regexify('[A-Z]{2}[0-9]{4}[A-Z]{2}');
    }

    public static function mileageKm(int $min = 15_000, int $max = 180_000): int
    {
        return self::uk()->numberBetween($min, $max);
    }

    public static function serviceStationName(): string
    {
        return sprintf('СТО %s', self::uk()->company());
    }
}
