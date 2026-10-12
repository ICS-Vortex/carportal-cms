<?php

declare(strict_types=1);

namespace App\Admin\Notification;

/**
 * Add a new admin notification source by implementing this interface and registering
 * the class as a service (autoconfigured via _instanceof in services.yaml).
 *
 * Example: a future ServiceLocationSubmissionProvider with type "service_location_pending".
 */
interface AdminNotificationProviderInterface
{
    /** Stable machine id, e.g. maintenance_plan_request */
    public function getType(): string;

    /** Translation key in the messages domain for the feed section title. */
    public function getTitleTranslationKey(): string;

    /** EasyAdmin icon: Font Awesome (e.g. fa-solid fa-inbox) or internal:* from assets/icons/internal/ */
    public function getIcon(): string;

    /** Higher values appear first in the bell menu. */
    public function getPriority(): int;

    public function getUnreadCount(): int;

    /**
     * @return list<AdminNotificationItem>
     */
    public function getRecentItems(int $limit): array;

    public function getViewAllUrl(): ?string;
}
