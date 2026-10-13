<?php

declare(strict_types=1);

namespace App\Admin\Appointment;

use App\Entity\MaintenanceProcedure;
use App\Entity\ServiceStation;
use App\Entity\Vehicle;
use App\Entity\VehicleGeneration;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class ServiceAppointmentDraft
{
    public ?ServiceStation $station = null;

    public ?Vehicle $vehicle = null;

    public ?\DateTimeImmutable $serviceDate = null;

    public ?string $serviceTime = null;

    public ?string $notesUk = null;

    /** @var list<MaintenanceProcedure> */
    public array $procedures = [];

    public ?int $estimatedCostUah = null;

    public bool $registerNewVehicle = false;

    public ?string $newOwnerEmail = null;

    public ?string $newOwnerName = null;

    public ?string $newLicensePlate = null;

    public ?int $newYear = null;

    public ?VehicleGeneration $newGeneration = null;

    public int $newMileageKm = 0;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->station === null) {
            $context->buildViolation('Оберіть СТО.')
                ->atPath('station')
                ->addViolation();
        }

        if ($this->registerNewVehicle) {
            if ($this->newOwnerEmail === null || trim($this->newOwnerEmail) === '') {
                $context->buildViolation('Вкажіть email власника.')
                    ->atPath('newOwnerEmail')
                    ->addViolation();
            }

            if ($this->newOwnerName === null || trim($this->newOwnerName) === '') {
                $context->buildViolation('Вкажіть імʼя власника.')
                    ->atPath('newOwnerName')
                    ->addViolation();
            }

            if ($this->newLicensePlate === null || trim($this->newLicensePlate) === '') {
                $context->buildViolation('Вкажіть держномер.')
                    ->atPath('newLicensePlate')
                    ->addViolation();
            }

            if ($this->newYear === null || $this->newYear < 1980 || $this->newYear > 2100) {
                $context->buildViolation('Вкажіть рік випуску.')
                    ->atPath('newYear')
                    ->addViolation();
            }

            if ($this->newGeneration === null) {
                $context->buildViolation('Оберіть покоління / модель авто.')
                    ->atPath('newGeneration')
                    ->addViolation();
            }

            return;
        }

        if ($this->vehicle === null) {
            $context->buildViolation('Оберіть автомобіль або увімкніть додавання нового.')
                ->atPath('vehicle')
                ->addViolation();
        }
    }
}
