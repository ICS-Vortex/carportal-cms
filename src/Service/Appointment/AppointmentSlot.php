<?php

declare(strict_types=1);

namespace App\Service\Appointment;

final class AppointmentSlot
{
    public function __construct(
        public readonly string $time,
        public readonly bool $available,
        public readonly int $bookedCount,
        public readonly int $capacity,
    ) {
    }
}
