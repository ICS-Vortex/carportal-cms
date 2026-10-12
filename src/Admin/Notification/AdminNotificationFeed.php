<?php

declare(strict_types=1);

namespace App\Admin\Notification;

final readonly class AdminNotificationFeed
{
    /**
     * @param list<AdminNotificationItem> $items
     */
    public function __construct(
        public string $type,
        public string $title,
        public int $unreadCount,
        public array $items,
        public ?string $viewAllUrl,
        public string $icon = 'fa-solid fa-bell',
    ) {
    }
}
