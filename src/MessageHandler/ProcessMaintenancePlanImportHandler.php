<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\MaintenancePlanImport;
use App\Entity\MaintenancePlanRequest;
use App\Maintenance\PlanImport\MaintenancePlanSpreadsheetParser;
use App\Maintenance\PlanImport\VehicleMaintenancePlanImportWriter;
use App\Message\ProcessMaintenancePlanImportMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessMaintenancePlanImportHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MaintenancePlanSpreadsheetParser $parser,
        private readonly VehicleMaintenancePlanImportWriter $planWriter,
        private readonly string $planImportStorageDir,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(ProcessMaintenancePlanImportMessage $message): void
    {
        $import = $this->entityManager->find(MaintenancePlanImport::class, $message->importId);
        if (!$import instanceof MaintenancePlanImport) {
            $this->logger->warning('Plan import not found', ['importId' => $message->importId]);

            return;
        }

        $import->setStatus(MaintenancePlanImport::STATUS_PROCESSING);
        $import->setStartedAt(new \DateTimeImmutable());
        $import->appendLog(sprintf('[%s] Початок обробки', date('c')));
        $this->entityManager->flush();

        try {
            $absolutePath = $this->planImportStorageDir.'/'.$import->getStoredPath();
            if (!is_readable($absolutePath)) {
                throw new \RuntimeException('Файл імпорту не знайдено на диску.');
            }

            $extension = pathinfo($import->getOriginalFilename(), PATHINFO_EXTENSION);
            $rows = $this->parser->parse($absolutePath, $extension);
            $import->setRowsTotal(\count($rows));
            $import->appendLog(sprintf('Розпізнано рядків: %d', \count($rows)));

            $vehicle = $import->getVehicle();
            if ($vehicle === null) {
                throw new \RuntimeException('Автомобіль не вказано.');
            }

            $templateName = sprintf('Імпорт: %s', $import->getOriginalFilename());
            $imported = $this->planWriter->replacePlanForVehicle($vehicle, $rows, $templateName);
            $import->setRowsImported($imported);
            $import->appendLog(sprintf('Записано робіт у план авто: %d', $imported));

            $planRequest = $import->getPlanRequest();
            if ($planRequest instanceof MaintenancePlanRequest) {
                $planRequest->setStatus('resolved');
                $planRequest->setHandledAt(new \DateTimeImmutable());
                $import->appendLog('Запит на план позначено як опрацьований.');
            }

            $import->setStatus(MaintenancePlanImport::STATUS_COMPLETED);
            $import->appendLog(sprintf('[%s] Успішно завершено', date('c')));
        } catch (\Throwable $exception) {
            $import->setStatus(MaintenancePlanImport::STATUS_FAILED);
            $import->setErrorMessage($exception->getMessage());
            $import->appendLog('Помилка: '.$exception->getMessage());
            $this->logger->error('Maintenance plan import failed', [
                'importId' => $message->importId,
                'exception' => $exception,
            ]);
        } finally {
            $import->setFinishedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }
}
