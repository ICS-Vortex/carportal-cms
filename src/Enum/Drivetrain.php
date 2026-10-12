<?php

declare(strict_types=1);

namespace App\Enum;

enum Drivetrain: string
{
    case RearWheel = 'rwd';
    case AllWheel = 'awd';
    case FrontWheel = 'fwd';

    public function label(): string
    {
        return match ($this) {
            self::RearWheel => 'RWD',
            self::AllWheel => 'AWD',
            self::FrontWheel => 'FWD',
        };
    }
}
