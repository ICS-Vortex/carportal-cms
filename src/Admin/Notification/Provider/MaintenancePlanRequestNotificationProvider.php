<?php

declare(strict_types=1);

namespace App\Admin\Notification\Provider;

use App\Admin\Notification\AdminNotificationItem;
use App\Admin\Notification\AdminNotificationProviderInterface;
use App\Controller\Admin\MaintenancePlanRequestCrudController;
use App\Entity\MaintenancePlanRequest;
use App\Repository\MaintenancePlanRequestRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MaintenancePlanRequestNotificationProvider implements AdminNotificationProviderInterface
{
    public const TYPE = 'maintenance_plan_request';

    public function __construct(
        private MaintenancePlanRequestRepository $planRequests,
        private AdminUrlGenerator $adminUrlGenerator,
        private TranslatorInterface $translator,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function getTitleTranslationKey(): string
    {
        return 'notifications.feeds.maintenance_plan_request.title';
    }

    public function getIcon(): string
    {
        return 'fa-solid fa-inbox';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getUnreadCount(): int
    {
        return $this->planRequests->countOpen();
    }

    public function getRecentItems(int $limit): array
    {
        $items = [];
        foreach ($this->planRequests->findOpenRecent($limit) as $request) {
            $items[] = $this->mapRequestToItem($request);
        }

        return $items;
    }

    public function getViewAllUrl(): ?string
    {
        return $this->adminUrlGenerator
            ->setController(MaintenancePlanRequestCrudController::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }

    private function mapRequestToItem(MaintenancePlanRequest $request): AdminNotificationItem
    {
        $vehicle = $request->getVehicle();
        $vehicleLabel = $vehicle?->getVin()
            ?? $vehicle?->getLicensePlate()
            ?? (string) $vehicle;

        $label = $this->translator->trans(
            'notifications.feeds.maintenance_plan_request.item',
            [
                '%owner%' => $request->getUser()?->getFullName() ?? '—',
                '%vehicle%' => $vehicleLabel,
            ],
            'messages',
        );

        $url = $this->adminUrlGenerator
            ->setController(MaintenancePlanRequestCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($request->getId())
            ->generateUrl();

        return new AdminNotificationItem(
            label: $label,
            url: $url,
            occurredAt: $request->getCreatedAt(),
        );
    }
}
