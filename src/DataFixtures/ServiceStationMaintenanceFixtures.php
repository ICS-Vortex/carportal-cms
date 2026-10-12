<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use App\Entity\MaintenanceScheduleItem;
use App\Entity\MaintenanceScheduleTemplate;
use App\Entity\ServiceStation;
use App\Entity\VehicleConfiguration;
use App\Entity\VehicleGeneration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * Station-scoped maintenance templates and labor prices for Carmain Київ / Carmain Львів.
 */
final class ServiceStationMaintenanceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const TEMPLATE_KYIV_HRV = 'template.station.kyiv.hrv';
    public const TEMPLATE_LVIV_HRV = 'template.station.lviv.hrv';
    public const TEMPLATE_KYIV_COROLLA_HYBRID = 'template.station.kyiv.corolla-hybrid';
    public const TEMPLATE_LVIV_CX5 = 'template.station.lviv.cx5';

    public function getDependencies(): array
    {
        return [
            ServiceStationFixtures::class,
            HondaHrvRu1MaintenanceFixture::class,
            VehicleConfigurationFixtures::class,
            MaintenanceProcedureFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['maintenance', 'catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $kyiv = $this->getReference(ServiceStationFixtures::KYIV_CENTER, ServiceStation::class);
        $lviv = $this->getReference(ServiceStationFixtures::LVIV_CENTER, ServiceStation::class);

        $kyivHrv = $this->createTemplate(
            $kyiv,
            HondaHrvRu1MaintenanceFixture::GENERATION,
            HondaHrvRu1MaintenanceFixture::CONFIG_CVT,
            'HR-V RU1 CVT · Carmain Київ',
            'Регламент для СТО на Хрещатику: коротші інтервали в місті та ціни мережі Києва.'
        );
        $this->addItems($manager, $kyivHrv, [
            HondaHrvRu1MaintenanceFixture::PROCEDURE_ENGINE_OIL => [8_000, 12, 890],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_OIL_FILTER => [8_000, 12, 320],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_CABIN_FILTER => [12_000, 12, 450],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_BRAKE_FLUID => [null, 36, 680],
        ]);

        $lvivHrv = $this->createTemplate(
            $lviv,
            HondaHrvRu1MaintenanceFixture::GENERATION,
            HondaHrvRu1MaintenanceFixture::CONFIG_CVT,
            'HR-V RU1 CVT · Carmain Львів',
            'Регламент для СТО на Городоцькій: стандартні інтервали та прайс філії Львова.'
        );
        $this->addItems($manager, $lvivHrv, [
            HondaHrvRu1MaintenanceFixture::PROCEDURE_ENGINE_OIL => [10_000, 12, 1_050],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_OIL_FILTER => [10_000, 12, 380],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_CABIN_FILTER => [15_000, 12, 520],
            HondaHrvRu1MaintenanceFixture::PROCEDURE_BRAKE_FLUID => [null, 36, 750],
        ]);

        $kyivHybrid = $this->createTemplate(
            $kyiv,
            VehicleGenerationFixtures::E210,
            VehicleConfigurationFixtures::COROLLA_HYBRID,
            'Corolla 1.8 Hybrid · Carmain Київ',
            'Гібридний регламент тільки для київської станції.'
        );
        $this->addItems($manager, $kyivHybrid, [
            MaintenanceProcedureFixtures::ENGINE_OIL => [15_000, 12, 920],
            MaintenanceProcedureFixtures::CABIN_FILTER => [15_000, 12, 480],
            MaintenanceProcedureFixtures::BRAKE_FLUID => [null, 24, 700],
            MaintenanceProcedureFixtures::SPARK_PLUGS => [60_000, 72, 1_400],
        ]);

        $lvivCx5 = $this->createTemplate(
            $lviv,
            VehicleGenerationFixtures::KF,
            VehicleConfigurationFixtures::CX5_AWD,
            'CX-5 2.5 AWD · Carmain Львів',
            'Повний регламент для CX-5 на львівському СТО.'
        );
        $this->addItems($manager, $lvivCx5, [
            MaintenanceProcedureFixtures::ENGINE_OIL => [10_000, 12, 1_180],
            MaintenanceProcedureFixtures::AIR_FILTER => [20_000, 24, 420],
            MaintenanceProcedureFixtures::CABIN_FILTER => [15_000, 12, 540],
            MaintenanceProcedureFixtures::BRAKE_FLUID => [null, 24, 820],
        ]);

        foreach ([$kyivHrv, $lvivHrv, $kyivHybrid, $lvivCx5] as $template) {
            $manager->persist($template);
        }

        $manager->flush();

        $this->addReference(self::TEMPLATE_KYIV_HRV, $kyivHrv);
        $this->addReference(self::TEMPLATE_LVIV_HRV, $lvivHrv);
        $this->addReference(self::TEMPLATE_KYIV_COROLLA_HYBRID, $kyivHybrid);
        $this->addReference(self::TEMPLATE_LVIV_CX5, $lvivCx5);

        $this->seedStationLaborOverrides($manager->getConnection(), $kyiv, $lviv);
    }

    private function createTemplate(
        ServiceStation $station,
        string $generationReference,
        string $configurationReference,
        string $nameUk,
        string $notesUk,
    ): MaintenanceScheduleTemplate {
        $template = new MaintenanceScheduleTemplate();
        $template
            ->setStation($station)
            ->setGeneration($this->getReference($generationReference, VehicleGeneration::class))
            ->setConfiguration($this->getReference($configurationReference, VehicleConfiguration::class))
            ->setNameUk($nameUk)
            ->setNotesUk($notesUk);

        return $template;
    }

    /**
     * @param array<string, array{0: ?int, 1: ?int, 2: int}> $rows procedure ref => [km, months, labor UAH]
     */
    private function addItems(ObjectManager $manager, MaintenanceScheduleTemplate $template, array $rows): void
    {
        foreach ($rows as $procedureReference => [$km, $months, $laborUah]) {
            $item = new MaintenanceScheduleItem();
            $item
                ->setTemplate($template)
                ->setProcedure($this->getReference($procedureReference, MaintenanceProcedure::class))
                ->setIntervalKm($km)
                ->setIntervalMonths($months)
                ->setLaborPriceUah($laborUah);
            $manager->persist($item);
        }
    }

    private function seedStationLaborOverrides(Connection $connection, ServiceStation $kyiv, ServiceStation $lviv): void
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:sP');

        $overrides = [
            [$kyiv, HondaHrvRu1MaintenanceFixture::PROCEDURE_ENGINE_OIL, 850],
            [$kyiv, HondaHrvRu1MaintenanceFixture::PROCEDURE_OIL_FILTER, 300],
            [$lviv, HondaHrvRu1MaintenanceFixture::PROCEDURE_ENGINE_OIL, 1_020],
            [$lviv, HondaHrvRu1MaintenanceFixture::PROCEDURE_OIL_FILTER, 360],
        ];

        foreach ($overrides as [$station, $procedureReference, $price]) {
            $procedure = $this->getReference($procedureReference, MaintenanceProcedure::class);
            $connection->insert('maintenance_station_labor_prices', [
                'id' => Uuid::v4()->toRfc4122(),
                'station_id' => $station->getId()?->toRfc4122(),
                'procedure_id' => $procedure->getId()?->toRfc4122(),
                'schedule_item_id' => null,
                'labor_price_uah' => $price,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
