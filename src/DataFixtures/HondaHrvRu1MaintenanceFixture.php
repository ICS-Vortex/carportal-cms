<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\MaintenanceScheduleItem;
use App\Entity\MaintenanceScheduleTemplate;
use App\Entity\VehicleBrand;
use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use App\Entity\VehicleModel;
use App\Enum\FuelType;
use App\Enum\TransmissionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Єдине джерело каталогу та регламенту Honda HR-V (RU1) — fixtures/data/honda-hrv-ru1.json
 * (генерується scripts/generate-hrv-ru1-fixtures.py).
 */
final class HondaHrvRu1MaintenanceFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const GENERATION = 'honda_hrv.generation.ru1';
    public const CONFIG_CVT = 'honda_hrv.configuration.cvt';
    public const CONFIG_MKPP = 'honda_hrv.configuration.mkpp';
    public const TEMPLATE_CVT = 'honda_hrv.template.cvt';
    public const TEMPLATE_MKPP = 'honda_hrv.template.mkpp';
    public const PROCEDURE_ENGINE_OIL = 'honda_hrv.procedure.engine-oil';
    public const PROCEDURE_OIL_FILTER = 'honda_hrv.procedure.oil-filter';
    public const PROCEDURE_CABIN_FILTER = 'honda_hrv.procedure.cabin-filter';
    public const PROCEDURE_BRAKE_FLUID = 'honda_hrv.procedure.brake-fluid';

    private const PROCEDURE_REF_BY_CODE = [
        'hrv-ru1-заміна-моторної-оливи' => self::PROCEDURE_ENGINE_OIL,
        'hrv-ru1-заміна-фільтра-оливи-двигуна' => self::PROCEDURE_OIL_FILTER,
        'hrv-ru1-заміна-пилового-фільтра-салону' => self::PROCEDURE_CABIN_FILTER,
        'hrv-ru1-заміна-гальмівної-рідини-замінюється-через-кожні' => self::PROCEDURE_BRAKE_FLUID,
    ];

    public function getDependencies(): array
    {
        return [VehicleBrandFixtures::class];
    }

    public static function getGroups(): array
    {
        return ['catalog', 'maintenance'];
    }

    public function load(ObjectManager $manager): void
    {
        $path = \dirname(__DIR__, 2).'/fixtures/data/honda-hrv-ru1.json';
        if (!is_readable($path)) {
            throw new \RuntimeException(sprintf('Fixture data not found: %s', $path));
        }

        /** @var array<string, mixed> $data */
        $data = json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);

        $brand = $this->getReference(VehicleBrandFixtures::HONDA, VehicleBrand::class);
        $model = $this->upsertModel($manager, $brand, $data['catalog']['model']);
        $generation = $this->upsertGeneration($manager, $model, $data['catalog']['generation']);

        $configurations = [];
        foreach ($data['catalog']['configurations'] as $configRow) {
            $templateKey = (string) $configRow['template_key'];
            $configurations[$templateKey] = $this->upsertConfiguration($manager, $generation, $configRow);
        }

        $extraNotes = $this->formatInformationalNotes($data['informational_notes'] ?? []);

        $templates = [];
        foreach ($data['templates'] as $key => $templateRow) {
            $configuration = $configurations[$key] ?? null;
            $notes = trim(($templateRow['notes_uk'] ?? '').($extraNotes !== '' ? "\n\n".$extraNotes : ''));
            $templates[$key] = $this->upsertTemplate(
                $manager,
                $generation,
                $configuration,
                $templateRow['name_uk'],
                $notes !== '' ? $notes : null,
            );
        }

        foreach ($data['procedures'] as $row) {
            $procedure = $this->upsertProcedure($manager, $row);

            $reference = self::PROCEDURE_REF_BY_CODE[$row['code']] ?? null;
            if (null !== $reference) {
                $this->addReference($reference, $procedure);
            }

            foreach ($templates as $templateKey => $template) {
                if (!($row['templates'][$templateKey] ?? false)) {
                    continue;
                }
                $this->upsertScheduleItem($manager, $template, $procedure, $row);
            }
        }

        $manager->flush();

        $this->addReference(self::GENERATION, $generation);
        if (isset($configurations['cvt'])) {
            $this->addReference(self::CONFIG_CVT, $configurations['cvt']);
        }
        if (isset($configurations['mkpp'])) {
            $this->addReference(self::CONFIG_MKPP, $configurations['mkpp']);
        }
        if (isset($templates['cvt'])) {
            $this->addReference(self::TEMPLATE_CVT, $templates['cvt']);
        }
        if (isset($templates['mkpp'])) {
            $this->addReference(self::TEMPLATE_MKPP, $templates['mkpp']);
        }
    }

    /**
     * @param array{slug: string, name: string} $row
     */
    private function upsertModel(ObjectManager $manager, VehicleBrand $brand, array $row): VehicleModel
    {
        $repo = $manager->getRepository(VehicleModel::class);
        $entity = $repo->findOneBy(['slug' => $row['slug'], 'brand' => $brand]) ?? new VehicleModel();
        $entity->setBrand($brand)->setSlug($row['slug'])->setName($row['name']);
        $manager->persist($entity);

        return $entity;
    }

    /**
     * @param array{slug: string, name: string, year_from: int, year_to: int, notes_uk?: string|null} $row
     */
    private function upsertGeneration(ObjectManager $manager, VehicleModel $model, array $row): VehicleGeneration
    {
        $repo = $manager->getRepository(VehicleGeneration::class);
        $entity = $repo->findOneBy(['slug' => $row['slug'], 'model' => $model]) ?? new VehicleGeneration();
        $entity
            ->setModel($model)
            ->setSlug($row['slug'])
            ->setName($row['name'])
            ->setYearFrom((int) $row['year_from'])
            ->setYearTo((int) $row['year_to'])
            ->setNotesUk($row['notes_uk'] ?? null);
        $manager->persist($entity);

        return $entity;
    }

    /**
     * @param array{slug: string, engine_label: string, transmission_label: string, drivetrain_label?: string|null} $row
     */
    private function upsertConfiguration(ObjectManager $manager, VehicleGeneration $generation, array $row): VehicleConfiguration
    {
        $repo = $manager->getRepository(VehicleConfiguration::class);
        $entity = $repo->findOneBy(['slug' => $row['slug'], 'generation' => $generation]) ?? new VehicleConfiguration();
        $entity
            ->setGeneration($generation)
            ->setSlug($row['slug'])
            ->setEngineLabel($row['engine_label'])
            ->setTransmissionLabel($this->mapTransmission((string) $row['transmission_label']))
            ->setDrivetrainLabel($row['drivetrain_label'] ?? null)
            ->setFuelType($this->mapFuel((string) $row['engine_label']));
        $manager->persist($entity);

        return $entity;
    }

    private function mapTransmission(string $label): TransmissionType
    {
        $normalized = mb_strtolower(trim($label));

        if (str_contains($normalized, 'cvt') || str_contains($normalized, 'варіатор')) {
            return TransmissionType::Cvt;
        }

        if (str_contains($normalized, 'мкпп') || str_contains($normalized, 'manual')) {
            return TransmissionType::Manual;
        }

        return TransmissionType::Manual;
    }

    private function mapFuel(string $engineLabel): FuelType
    {
        $normalized = mb_strtolower($engineLabel);

        if (str_contains($normalized, 'hybrid') || str_contains($normalized, 'гібрид')) {
            return FuelType::Hybrid;
        }

        if (str_contains($normalized, 'diesel') || str_contains($normalized, 'дизел')) {
            return FuelType::Diezel;
        }

        return FuelType::Benzin;
    }

    private function upsertTemplate(
        ObjectManager $manager,
        VehicleGeneration $generation,
        VehicleConfiguration $configuration,
        string $nameUk,
        ?string $notesUk,
    ): MaintenanceScheduleTemplate {
        $repo = $manager->getRepository(MaintenanceScheduleTemplate::class);
        $entity = $repo->findOneBy([
            'nameUk' => $nameUk,
            'generation' => $generation,
            'configuration' => $configuration,
        ]) ?? new MaintenanceScheduleTemplate();

        $entity
            ->setNameUk($nameUk)
            ->setGeneration($generation)
            ->setConfiguration($configuration)
            ->setNotesUk($notesUk)
            ->setDeleted(false)
            ->setDeletedAt(null);
        $manager->persist($entity);

        return $entity;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function upsertProcedure(ObjectManager $manager, array $row): MaintenanceProcedure
    {
        $repo = $manager->getRepository(MaintenanceProcedure::class);
        $entity = $repo->findOneBy(['code' => $row['code']]) ?? new MaintenanceProcedure();

        $entity
            ->setCode($row['code'])
            ->setTitleUk($row['title_uk'])
            ->setCategory($this->mapCategory((string) $row['category']))
            ->setDescriptionUk($row['description_uk'] ?? null)
            ->setDefaultIntervalKm(isset($row['interval_km']) ? (int) $row['interval_km'] : null)
            ->setDefaultIntervalMonths(isset($row['interval_months']) ? (int) $row['interval_months'] : null)
            ->setLaborPriceUah(isset($row['labor_price_uah']) ? (int) $row['labor_price_uah'] : null)
            ->setDeleted(false)
            ->setDeletedAt(null);
        $manager->persist($entity);

        return $entity;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function upsertScheduleItem(
        ObjectManager $manager,
        MaintenanceScheduleTemplate $template,
        MaintenanceProcedure $procedure,
        array $row,
    ): void {
        $repo = $manager->getRepository(MaintenanceScheduleItem::class);
        $entity = $repo->findOneBy([
            'template' => $template,
            'procedure' => $procedure,
        ]) ?? new MaintenanceScheduleItem();

        $entity
            ->setTemplate($template)
            ->setProcedure($procedure)
            ->setIntervalKm(isset($row['interval_km']) ? (int) $row['interval_km'] : null)
            ->setIntervalMonths(isset($row['interval_months']) ? (int) $row['interval_months'] : null)
            ->setNotesUk($row['description_uk'] ?? null)
            ->setLaborPriceUah(isset($row['labor_price_uah']) ? (int) $row['labor_price_uah'] : null)
            ->setDeleted(false)
            ->setDeletedAt(null);
        $manager->persist($entity);
    }

    private function mapCategory(string $category): string
    {
        return match ($category) {
            'control' => 'Контрольні роботи',
            'extra' => 'Додаткові операції',
            default => 'Регламентні роботи',
        };
    }

    /**
     * @param list<array{title_uk: string, note_uk: string}> $rows
     */
    private function formatInformationalNotes(array $rows): string
    {
        $chunks = [];
        foreach ($rows as $row) {
            $chunks[] = $row['title_uk'].': '.$row['note_uk'];
        }

        return implode("\n", $chunks);
    }
}
