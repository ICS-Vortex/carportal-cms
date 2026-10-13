<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Admin\Appointment\ServiceAppointmentDraft;
use App\Entity\ServiceLog;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Form\ServiceAppointmentCreateType;
use App\Repository\ServiceLogRepository;
use App\Service\Admin\ServiceStationAccess;
use App\Service\Appointment\AppointmentBookingService;
use App\Service\Appointment\AppointmentLaborEstimateCalculator;
use App\Service\Appointment\AppointmentSlot;
use App\Service\Appointment\AppointmentSlotCalculator;
use App\Service\Appointment\KyivClock;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

#[IsGranted('ROLE_ADMIN')]
final class ServiceAppointmentCalendarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ServiceLogRepository $serviceLogs,
        private readonly AppointmentSlotCalculator $slotCalculator,
        private readonly AppointmentBookingService $bookingService,
        private readonly AppointmentLaborEstimateCalculator $estimateCalculator,
        private readonly ServiceStationAccess $stationAccess,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[Route('/admin/appointments', name: 'admin_appointments_calendar', methods: ['GET', 'POST'])]
    public function calendar(Request $request): Response
    {
        $actor = $this->requireAdminUser();
        $stations = $this->stationAccess->accessibleActiveStations($actor);

        if ($stations === []) {
            $this->addFlash('warning', 'Немає доступних СТО. Зверніться до головного адміністратора.');

            return $this->render('admin/service_appointment_calendar.html.twig', [
                'stations' => [],
                'station' => null,
                'form' => null,
            ]);
        }

        $stationId = $request->request->getString('station', $request->query->getString('station', (string) $stations[0]->getId()));
        $station = $this->entityManager->find(ServiceStation::class, Uuid::fromString($stationId));

        if (!$station instanceof ServiceStation || !$this->stationAccess->canAccessStation($actor, $station)) {
            $station = $stations[0];
        }

        $draft = new ServiceAppointmentDraft();
        $draft->station = $station;
        $prefillDate = $request->query->getString('prefill_date');
        $prefillTime = $request->query->getString('prefill_time');

        if ($prefillDate !== '') {
            $parsedDate = \DateTimeImmutable::createFromFormat('Y-m-d', $prefillDate);

            if ($parsedDate instanceof \DateTimeImmutable) {
                $draft->serviceDate = $parsedDate;
            }
        }

        if ($draft->serviceDate === null) {
            $draft->serviceDate = \DateTimeImmutable::createFromFormat('Y-m-d', KyivClock::todayDateString()) ?: KyivClock::now();
        }

        if ($prefillTime !== '') {
            $draft->serviceTime = substr($prefillTime, 0, 5);
        }

        $this->applySuggestedEstimate($draft);

        $slotStation = $draft->station ?? $station;
        $slotChoices = $this->buildSlotChoices($this->slotCalculator->slotsForDay($slotStation, $draft->serviceDate));

        $form = $this->createForm(ServiceAppointmentCreateType::class, $draft, [
            'slot_choices' => $slotChoices,
            'booking_actor' => $actor,
            'booking_station' => $draft->station ?? $station,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $slotStation = $draft->station ?? $station;

            if ($draft->serviceDate !== null) {
                $slotChoices = $this->buildSlotChoices($this->slotCalculator->slotsForDay($slotStation, $draft->serviceDate));
            }

            $this->applySuggestedEstimate($draft);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->stationAccess->assertCanAccessStation($actor, $draft->station);

            $result = $this->bookingService->book($draft, $actor);

            if ($result['ok'] === false) {
                $this->addFlash('danger', $result['message']);
            } else {
                $this->addFlash('success', 'Запис на СТО збережено.');

                return $this->redirectToRoute('admin_appointments_calendar', [
                    'station' => (string) $draft->station?->getId(),
                    'week' => $draft->serviceDate?->format('Y-m-d'),
                ]);
            }
        }

        $calendarStation = $draft->station ?? $station;

        $editStationUrl = $this->adminUrlGenerator
            ->setController(ServiceStationCrudController::class)
            ->setAction('edit')
            ->setEntityId((string) $calendarStation->getId())
            ->generateUrl();

        $blocksUrl = $this->adminUrlGenerator
            ->setController(ServiceStationScheduleBlockCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->render('admin/service_appointment_calendar.html.twig', [
            'stations' => $stations,
            'station' => $calendarStation,
            'form' => $form,
            'formServiceDate' => $draft->serviceDate?->format('Y-m-d') ?? KyivClock::todayDateString(),
            'formStationId' => (string) $calendarStation->getId(),
            'prefillTime' => $draft->serviceTime ?? $prefillTime,
            'openBookingModal' => $form->isSubmitted() || $request->query->getBoolean('open_booking'),
            'editStationUrl' => $editStationUrl,
            'blocksUrl' => $blocksUrl,
            'calendarCsrf' => $this->csrfTokenManager->getToken('appointments-calendar')->getValue(),
            'eventsUrl' => $this->generateUrl('admin_appointments_events'),
            'rescheduleUrlTemplate' => $this->generateUrl('admin_appointments_reschedule', ['id' => '__ID__']),
            'initialDate' => $this->resolveAnchorDate($request)->format('Y-m-d'),
            'slotMinutes' => $calendarStation->getAppointmentSlotMinutes(),
            'showStationToolbar' => count($stations) > 1,
        ]);
    }

    #[Route('/admin/appointments/{id}/cancel', name: 'admin_appointments_cancel', methods: ['POST'])]
    public function cancel(Request $request, string $id): Response
    {
        if (!$this->isCsrfTokenValid('cancel-appointment', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $actor = $this->requireAdminUser();
        $log = $this->entityManager->find(ServiceLog::class, Uuid::fromString($id));

        if (!$log instanceof ServiceLog || $log->getRecordKind() !== ServiceLog::RECORD_KIND_APPOINTMENT) {
            throw $this->createNotFoundException();
        }

        $this->stationAccess->assertCanAccessAppointment($actor, $log);

        $stationId = (string) $log->getServiceStationEntity()?->getId();
        $week = $log->getServiceDate()?->format('Y-m-d');

        $this->entityManager->remove($log);
        $this->entityManager->flush();

        $this->addFlash('success', 'Запис скасовано.');

        return $this->redirectToRoute('admin_appointments_calendar', [
            'station' => $stationId,
            'week' => $week,
        ]);
    }

    private function requireAdminUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function applySuggestedEstimate(ServiceAppointmentDraft $draft): void
    {
        if ($draft->station === null || $draft->procedures === []) {
            return;
        }

        if ($draft->estimatedCostUah === null) {
            $draft->estimatedCostUah = $this->estimateCalculator->estimate($draft->station, $draft->procedures);
        }
    }

    private function resolveAnchorDate(Request $request): \DateTimeImmutable
    {
        $week = $request->query->getString('week');

        if ($week !== '') {
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $week);

            if ($parsed instanceof \DateTimeImmutable) {
                return $parsed;
            }
        }

        return KyivClock::now();
    }

    /**
     * @param list<AppointmentSlot> $slots
     *
     * @return array<string, string>
     */
    private function buildSlotChoices(array $slots): array
    {
        $choices = [];

        foreach ($slots as $slot) {
            if (!$slot->available) {
                continue;
            }

            $free = $slot->capacity - $slot->bookedCount;
            $suffix = $slot->capacity > 1 ? sprintf(' (%d вільно)', $free) : '';
            $choices[$slot->time.$suffix] = $slot->time;
        }

        return $choices;
    }
}
