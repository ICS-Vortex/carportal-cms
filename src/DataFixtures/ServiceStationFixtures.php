<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ServiceStation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class ServiceStationFixtures extends Fixture implements FixtureGroupInterface
{
    public const KYIV_CENTER = 'service_station.kyiv-center';
    public const LVIV_CENTER = 'service_station.lviv-center';

    public static function getGroups(): array
    {
        return ['catalog'];
    }

    public function load(ObjectManager $manager): void
    {
        $stations = [
            self::KYIV_CENTER => $this->station('kyiv-center', 'Carmain Київ', 'Київ', 'вул. Хрещатик, 1', 10),
            self::LVIV_CENTER => $this->station('lviv-center', 'Carmain Львів', 'Львів', 'вул. Городоцька, 100', 20),
        ];

        foreach ($stations as $reference => $entity) {
            $manager->persist($entity);
            $this->addReference($reference, $entity);
        }

        $manager->flush();
    }

    private function station(string $slug, string $name, string $city, string $address, int $sortOrder): ServiceStation
    {
        $station = new ServiceStation();
        $station
            ->setSlug($slug)
            ->setNameUk($name)
            ->setCityUk($city)
            ->setAddressUk($address)
            ->setSortOrder($sortOrder)
            ->setIsActive(true)
            ->setAppointmentSlotMinutes($slug === 'lviv-center' ? 45 : 60)
            ->setAppointmentDayStart(new \DateTimeImmutable('09:00:00'))
            ->setAppointmentDayEnd(new \DateTimeImmutable('18:00:00'))
            ->setAppointmentSlotCapacity($slug === 'kyiv-center' ? 3 : 1);

        return $station;
    }
}
