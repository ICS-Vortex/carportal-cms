<?php

declare(strict_types=1);

namespace App\EasyAdmin;

trait EnumChoices
{
    /**
     * @param class-string<\BackedEnum> $enumClass
     *
     * @return array<string, \BackedEnum>
     */
    private function enumChoices(string $enumClass): array
    {
        $choices = [];
        foreach ($enumClass::cases() as $case) {
            $label = method_exists($case, 'label') ? $case->label() : $case->name;
            $choices[$label] = $case;
        }

        return $choices;
    }
}
