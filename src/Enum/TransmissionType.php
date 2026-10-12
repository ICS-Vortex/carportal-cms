<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatorInterface;

enum TransmissionType: string
{
    case Manual = 'manual';
    case Cvt = 'cvt';
    case Auto = 'auto';
    case ECvt = 'ecvt';
    case Dct = 'dct';
    case Amt = 'amt';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Cvt => 'CVT',
            self::Auto => 'Auto',
            self::ECvt => 'e-CVT',
            self::Dct => 'DCT',
            self::Amt => 'AMT',
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
            $choices[$translator->trans('enum.transmission.'.$case->value, domain: 'messages')] = $case;
        }

        return $choices;
    }
}
