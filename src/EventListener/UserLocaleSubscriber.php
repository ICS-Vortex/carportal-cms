<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\LocaleSwitcher;

final class UserLocaleSubscriber implements EventSubscriberInterface
{
    /** @var list<string> */
    private const SUPPORTED = ['en', 'uk'];

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private LocaleSwitcher $localeSwitcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // After firewall (8) and LocaleListener (16), so the user is authenticated.
            KernelEvents::REQUEST => ['onKernelRequest', 6],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user instanceof User) {
            return;
        }

        $locale = $user->getLocale();
        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        $event->getRequest()->setLocale($locale);
        $this->localeSwitcher->setLocale($locale);
    }
}
