<?php

declare(strict_types=1);

namespace App\Admin\Notification;

final readonly class AdminNotificationItem
{
    public function __construct(
        public string $label,
        public string $url,
        public ?\DateTimeInterface $occurredAt = null,
    ) {
    }
}
