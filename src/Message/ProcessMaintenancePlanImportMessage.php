<?php

declare(strict_types=1);

namespace App\Message;

final readonly class ProcessMaintenancePlanImportMessage
{
    public function __construct(
        public string $importId,
    ) {
    }
}
