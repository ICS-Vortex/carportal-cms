<?php

declare(strict_types=1);

namespace App\Enum;

enum BodyStyle: string
{
    case Coupe = 'coupe';
    case Sedan = 'sedan';
    case Suv = 'suv';
    case Roadster = 'roadster';
    case Wagon = 'wagon';

    public function label(): string
    {
        return match ($this) {
            self::Coupe => 'Coupe',
            self::Sedan => 'Sedan',
            self::Suv => 'SUV',
            self::Roadster => 'Roadster',
            self::Wagon => 'Wagon',
        };
    }
}
