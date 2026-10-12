<?php

declare(strict_types=1);

namespace App\Admin\Notification;

use Symfony\Contracts\Translation\TranslatorInterface;

final class AdminNotificationHub
{
    /** @var list<AdminNotificationProviderInterface> */
    private array $providers;

    /**
     * @param iterable<AdminNotificationProviderInterface> $providers
     */
    public function __construct(
        iterable $providers,
        private TranslatorInterface $translator,
    ) {
        $this->providers = $this->sortProviders($providers);
    }

    public function totalUnreadCount(): int
    {
        $total = 0;
        foreach ($this->providers as $provider) {
            $total += $provider->getUnreadCount();
        }

        return $total;
    }

    public function unreadCountForType(string $type): int
    {
        foreach ($this->providers as $provider) {
            if ($provider->getType() === $type) {
                return $provider->getUnreadCount();
            }
        }

        return 0;
    }

    /**
     * @return list<AdminNotificationFeed>
     */
    public function feeds(int $itemsPerFeed = 8): array
    {
        $feeds = [];
        foreach ($this->providers as $provider) {
            $count = $provider->getUnreadCount();
            if ($count === 0) {
                continue;
            }

            $feeds[] = new AdminNotificationFeed(
                type: $provider->getType(),
                title: $this->translator->trans($provider->getTitleTranslationKey(), domain: 'messages'),
                unreadCount: $count,
                items: $provider->getRecentItems($itemsPerFeed),
                viewAllUrl: $provider->getViewAllUrl(),
                icon: $provider->getIcon(),
            );
        }

        return $feeds;
    }

    /**
     * @param iterable<AdminNotificationProviderInterface> $providers
     *
     * @return list<AdminNotificationProviderInterface>
     */
    private function sortProviders(iterable $providers): array
    {
        $list = iterator_to_array($providers, false);
        usort(
            $list,
            static fn (AdminNotificationProviderInterface $a, AdminNotificationProviderInterface $b): int => $b->getPriority() <=> $a->getPriority(),
        );

        return $list;
    }
}
