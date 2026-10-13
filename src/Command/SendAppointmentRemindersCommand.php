<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ServiceLog;
use App\Repository\ServiceLogRepository;
use App\Service\Appointment\AppointmentReminderMailer;
use App\Service\Appointment\KyivClock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:appointments:send-reminders',
    description: 'Send email reminders ~24h before scheduled service appointments',
)]
final class SendAppointmentRemindersCommand extends Command
{
    public function __construct(
        private readonly ServiceLogRepository $serviceLogs,
        private readonly AppointmentReminderMailer $mailer,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = KyivClock::now();
        $windowStart = $now->modify('+23 hours');
        $windowEnd = $now->modify('+25 hours');

        $candidates = $this->serviceLogs->findAppointmentsNeedingReminder($windowStart, $windowEnd);
        $sent = 0;

        foreach ($candidates as $log) {
            if (!$log instanceof ServiceLog) {
                continue;
            }

            try {
                $this->mailer->send($log);
                $log->setAppointmentReminderSentAt(KyivClock::now());
                ++$sent;
            } catch (\Throwable $exception) {
                $io->warning(sprintf('Не вдалося надіслати для %s: %s', (string) $log->getId(), $exception->getMessage()));
            }
        }

        $this->entityManager->flush();
        $io->success(sprintf('Надіслано нагадувань: %d', $sent));

        return Command::SUCCESS;
    }
}
