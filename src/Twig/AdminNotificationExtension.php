<?php

declare(strict_types=1);

namespace App\Twig;

use App\Admin\Notification\AdminNotificationFeed;
use App\Admin\Notification\AdminNotificationHub;
use Twig\Attribute\AsTwigFunction;

final class AdminNotificationExtension
{
    public function __construct(
        private AdminNotificationHub $notifications,
    ) {
    }

    #[AsTwigFunction('admin_notification_count')]
    public function notificationCount(): int
    {
        return $this->notifications->totalUnreadCount();
    }

    /**
     * @return list<AdminNotificationFeed>
     */
    #[AsTwigFunction('admin_notification_feeds')]
    public function notificationFeeds(int $itemsPerFeed = 8): array
    {
        return $this->notifications->feeds($itemsPerFeed);
    }

    #[AsTwigFunction('admin_notification_count_for_type')]
    public function notificationCountForType(string $type): int
    {
        return $this->notifications->unreadCountForType($type);
    }
}
