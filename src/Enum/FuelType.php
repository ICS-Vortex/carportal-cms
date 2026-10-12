<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

enum FuelType: string
{
    case Gas = 'gas';
    case Benzin = 'benzin';
    case Diezel = 'diezel';
    case Hybrid = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Gas => 'Gas',
            self::Benzin => 'Benzin',
            self::Diezel => 'Diezel',
            self::Hybrid => 'Hybrid',
        };
    }

    /**
     * @return array<string, self>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case;
        }

        return $choices;
    }

    /**
     * @return array<string, self>
     */
    public static function translatedChoices(TranslatorInterface $translator): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$translator->trans('enum.fuel.'.$case->value, domain: 'messages')] = $case;
        }

        return $choices;
    }
}
