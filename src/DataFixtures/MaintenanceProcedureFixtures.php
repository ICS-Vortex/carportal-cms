<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MaintenanceProcedure;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class MaintenanceProcedureFixtures extends Fixture implements FixtureGroupInterface
{
    public const ENGINE_OIL = 'procedure.engine-oil';
    public const OIL_FILTER = 'procedure.oil-filter';
    public const AIR_FILTER = 'procedure.air-filter';
    public const CABIN_FILTER = 'procedure.cabin-filter';
    public const BRAKE_FLUID = 'procedure.brake-fluid';
    public const SPARK_PLUGS = 'procedure.spark-plugs';

    public static function getGroups(): array
    {
        return ['maintenance'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();

        $procedures = [
            self::ENGINE_OIL => $this->procedure('engine-oil', 'Заміна моторної оливи', 'fluids', 10_000, 12, $faker->optional(0.7)->sentence(8)),
            self::OIL_FILTER => $this->procedure('oil-filter', 'Заміна оливного фільтра', 'fluids', 10_000, 12, null),
            self::AIR_FILTER => $this->procedure('air-filter', 'Повітряний фільтр', 'filters', 20_000, 24, null),
            self::CABIN_FILTER => $this->procedure('cabin-filter', 'Фільтр салону', 'filters', 15_000, 12, $faker->optional(0.8)->sentence(10)),
            self::BRAKE_FLUID => $this->procedure('brake-fluid', 'Гальмівна рідина', 'brakes', null, 24, $faker->optional(0.75)->sentence(9)),
            self::SPARK_PLUGS => $this->procedure('spark-plugs', 'Свічки запалювання', 'ignition', 40_000, 48, null),
        ];

        foreach ($procedures as $reference => $procedure) {
            $manager->persist($procedure);
            $this->addReference($reference, $procedure);
        }

        $manager->flush();
    }

    private function procedure(string $code, string $title, string $category, ?int $km, ?int $months, ?string $description): MaintenanceProcedure
    {
        $procedure = new MaintenanceProcedure();
        $procedure
            ->setCode($code)
            ->setTitleUk($title)
            ->setCategory($category)
            ->setDefaultIntervalKm($km)
            ->setDefaultIntervalMonths($months)
            ->setDescriptionUk($description);

        return $procedure;
    }
}
