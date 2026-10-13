<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Repository\ServiceLogRepository;
use App\Service\Admin\ServiceStationAccess;
use App\Service\Appointment\AppointmentRescheduleService;
use App\Service\Appointment\KyivClock;
use App\Service\Appointment\ScheduleBlockEvaluator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_ADMIN')]
final class ServiceAppointmentCalendarApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceLogRepository $serviceLogs,
        private readonly ScheduleBlockEvaluator $scheduleBlocks,
        private readonly AppointmentRescheduleService $rescheduleService,
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    #[Route('/admin/appointments/events', name: 'admin_appointments_events', methods: ['GET'])]
    public function events(Request $request): JsonResponse
    {
        $stationId = $request->query->getString('station');
        $station = $this->entityManager->find(ServiceStation::class, Uuid::fromString($stationId));

        if (!$station instanceof ServiceStation) {
            return new JsonResponse([]);
        }

        $actor = $this->getUser();

        if (!$actor instanceof User || !$this->stationAccess->canAccessStation($actor, $station)) {
            return new JsonResponse(['message' => 'Немає доступу до цього СТО.'], 403);
        }

        $start = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', substr($request->query->getString('start'), 0, 19)) ?: new \DateTimeImmutable($request->query->getString('start'));
        $end = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', substr($request->query->getString('end'), 0, 19)) ?: new \DateTimeImmutable($request->query->getString('end'));

        $events = [];

        foreach ($this->serviceLogs->findAppointmentsForStationBetween($station, $start, $end) as $log) {
            $events[] = $this->appointmentEvent($log, $station);
        }

        foreach ($this->scheduleBlocks->blocksBetween($station, $start, $end) as $block) {
            $blockEvent = $this->blockEvent($block);

            if ($blockEvent !== []) {
                $events[] = $blockEvent;
            }
        }

        return new JsonResponse($events);
    }

    #[Route('/admin/appointments/{id}/reschedule', name: 'admin_appointments_reschedule', methods: ['POST'])]
    public function reschedule(Request $request, string $id): JsonResponse
    {
        if (!$this->isCsrfTokenValid('appointments-calendar', (string) $request->headers->get('X-CSRF-TOKEN'))) {
            return new JsonResponse(['message' => 'Невалідний CSRF.'], 403);
        }

        $actor = $this->getUser();

        if (!$actor instanceof User) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        $log = $this->entityManager->find(ServiceLog::class, Uuid::fromString($id));

        if (!$log instanceof ServiceLog) {
            return new JsonResponse(['message' => 'Запис не знайдено.'], 404);
        }

        try {
            $this->stationAccess->assertCanAccessAppointment($actor, $log);
        } catch (\Throwable) {
            return new JsonResponse(['message' => 'Немає доступу до цього запису.'], 403);
        }

        $station = $log->getServiceStationEntity();

        if ($station === null) {
            return new JsonResponse(['message' => 'СТО не вказано.'], 400);
        }

        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $start = new \DateTimeImmutable((string) ($payload['start'] ?? ''));
        $day = $start->setTime(0, 0);
        $time = $start->format('H:i');

        $result = $this->rescheduleService->reschedule($log, $station, $day, $time);

        if ($result['ok'] === false) {
            return new JsonResponse(['message' => $result['message']], 400);
        }

        return new JsonResponse(['ok' => true]);
    }

    /** @return array<string, mixed> */
    private function appointmentEvent(ServiceLog $log, ServiceStation $station): array
    {
        $vehicle = $log->getVehicle();
        $generation = $vehicle?->getGeneration();
        $model = $generation?->getModel();
        $brand = $model?->getBrand();
        $title = trim(sprintf('%s %s', $brand?->getName() ?? '', $model?->getName() ?? ''));
        $date = $log->getServiceDate()?->format('Y-m-d') ?? KyivClock::todayDateString();
        $time = $log->getServiceTime()?->format('H:i') ?? '09:00';
        $start = new \DateTimeImmutable(sprintf('%s %s:00', $date, $time));
        $end = $start->modify(sprintf('+%d minutes', max(15, $station->getAppointmentSlotMinutes())));

        return [
            'id' => (string) $log->getId(),
            'title' => $title !== '' ? $title : 'Запис',
            'start' => $start->format(\DateTimeInterface::ATOM),
            'end' => $end->format(\DateTimeInterface::ATOM),
            'editable' => true,
            'backgroundColor' => '#c51b1b',
            'borderColor' => '#a31616',
            'extendedProps' => [
                'kind' => 'appointment',
                'plate' => $vehicle?->getLicensePlate(),
                'estimate' => $log->getEstimatedCostUah(),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function blockEvent(\App\Entity\ServiceStationScheduleBlock $block): array
    {
        $startDate = $block->getStartDate();
        $endDate = $block->getEndDate();

        if ($startDate === null || $endDate === null) {
            return [];
        }

        if ($block->isWholeDay()) {
            return [
                'id' => 'block-'.(string) $block->getId(),
                'title' => $block->getLabelUk(),
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->modify('+1 day')->format('Y-m-d'),
                'allDay' => true,
                'display' => 'background',
                'backgroundColor' => 'rgba(168, 168, 168, 0.14)',
                'editable' => false,
                'extendedProps' => ['kind' => 'block'],
            ];
        }

        $timeStart = $block->getStartTime()?->format('H:i') ?? '00:00';
        $timeEnd = $block->getEndTime()?->format('H:i') ?? '23:59';

        return [
            'id' => 'block-'.(string) $block->getId(),
            'title' => $block->getLabelUk(),
            'start' => sprintf('%sT%s:00', $startDate->format('Y-m-d'), $timeStart),
            'end' => sprintf('%sT%s:00', $endDate->format('Y-m-d'), $timeEnd),
            'display' => 'background',
            'backgroundColor' => 'rgba(224, 58, 58, 0.14)',
            'editable' => false,
            'extendedProps' => ['kind' => 'block'],
        ];
    }
}
