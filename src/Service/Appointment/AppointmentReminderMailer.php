<?php

declare(strict_types=1);

namespace App\Service\Appointment;

use App\Entity\ServiceLog;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final class AppointmentReminderMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $mailerFrom,
    ) {
    }

    public function send(ServiceLog $log): void
    {
        $vehicle = $log->getVehicle();
        $owner = $vehicle?->getUser();

        if ($vehicle === null || $owner === null || $owner->getEmail() === '') {
            return;
        }

        $generation = $vehicle->getGeneration();
        $model = $generation?->getModel();
        $brand = $model?->getBrand();
        $vehicleLabel = trim(sprintf('%s %s %d', $brand?->getName() ?? '', $model?->getName() ?? '', $vehicle->getYear()));

        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFrom, 'Carmain'))
            ->to($owner->getEmail())
            ->subject('Нагадування: візит на СТО завтра')
            ->htmlTemplate('email/appointment_reminder.html.twig')
            ->context([
                'ownerName' => $owner->getFullName(),
                'vehicleLabel' => $vehicleLabel,
                'stationName' => $log->getServiceStation() ?? 'СТО',
                'serviceDate' => $log->getServiceDate(),
                'serviceTime' => $log->getServiceTime(),
                'procedures' => array_map(
                    static fn ($item) => $item->getProcedure()?->getTitleUk() ?? '',
                    $log->getItems()->toArray()
                ),
            ]);

        $this->mailer->send($email);
    }
}
