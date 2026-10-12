<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AuthRefreshSession;
use App\Entity\User;
use App\Entity\UserGoogleAccount;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class UserFixtures extends Fixture implements FixtureGroupInterface
{
    public const ADMIN = 'user.admin';
    public const OLENA = 'user.olena';
    public const ANDRII = 'user.andrii';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire(env: 'CMS_ADMIN_EMAIL')]
        private string $adminEmail,
        #[Autowire(env: 'CMS_ADMIN_PASSWORD')]
        private string $adminPassword,
    ) {
    }

    public static function getGroups(): array
    {
        return ['users'];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FixtureFaker::uk();

        $admin = $this->user($this->adminEmail, $faker->name(), 'admin', $this->adminPassword);
        $olena = $this->user($faker->unique()->safeEmail(), $faker->name(), 'user', 'service123');
        $andrii = $this->user($faker->unique()->safeEmail(), $faker->name(), 'user', 'service123');

        $google = new UserGoogleAccount();
        $google->setUser($olena)->setEmail($olena->getEmail())->setGoogleSub($faker->uuid());

        $session = new AuthRefreshSession();
        $session
            ->setUser($andrii)
            ->setFamilyId(Uuid::v7())
            ->setTokenHash(hash('sha256', $faker->uuid()))
            ->setUserAgent($faker->userAgent())
            ->setExpiresAt(new \DateTimeImmutable($faker->dateTimeBetween('+7 days', '+60 days')->format('Y-m-d H:i:s')))
            ->setLastUsedAt(new \DateTimeImmutable($faker->dateTimeBetween('-14 days', '-1 day')->format('Y-m-d H:i:s')));

        $manager->persist($admin);
        $manager->persist($olena);
        $manager->persist($andrii);
        $manager->persist($google);
        $manager->persist($session);
        $manager->flush();

        $this->addReference(self::ADMIN, $admin);
        $this->addReference(self::OLENA, $olena);
        $this->addReference(self::ANDRII, $andrii);
    }

    private function user(string $email, string $name, string $role, string $password): User
    {
        $user = new User();
        $user
            ->setEmail($email)
            ->setFullName($name)
            ->setRole($role)
            ->setLocale('uk')
            ->setPassword($this->passwordHasher->hashPassword($user, $password));

        return $user;
    }
}
