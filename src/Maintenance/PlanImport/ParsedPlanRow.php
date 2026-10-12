<?php

declare(strict_types=1);

namespace App\Maintenance\PlanImport;

final readonly class ParsedPlanRow
{
    public function __construct(
        public int $lineNumber,
        public string $titleUk,
        public string $category,
        public ?int $intervalKm,
        public ?int $intervalMonths,
        public ?int $laborPriceUah,
        public ?string $notesUk,
        public ?string $procedureCode,
    ) {
    }
}
