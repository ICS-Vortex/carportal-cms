<?php

declare(strict_types=1);

namespace App\Message;

final class PublishVehicle
{
    public function __construct(
        public readonly int $vehicleId,
    ) {
    }
}
