<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Brand;
use App\Entity\Story;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enum\BodyStyle;
use App\Enum\Drivetrain;
use App\Enum\VehicleStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:bootstrap', description: 'Create the atelier editor and opening catalog when the database is empty.')]
final class BootstrapCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        #[Autowire(env: 'CMS_ADMIN_EMAIL')]
        private string $adminEmail,
        #[Autowire(env: 'CMS_ADMIN_PASSWORD')]
        private string $adminPassword,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $createdEditor = $this->ensureEditor();
        $createdCatalog = $this->ensureCatalog();

        if (!$createdEditor && !$createdCatalog) {
            $io->writeln('Atelier already has an editor and a catalog.');

            return Command::SUCCESS;
        }

        $this->entityManager->flush();

        if ($createdEditor) {
            $io->writeln(sprintf('Editor ready: %s', $this->adminEmail));
        }
        if ($createdCatalog) {
            $io->writeln('Opening catalog queued for the floor.');
        }

        return Command::SUCCESS;
    }

    private function ensureEditor(): bool
    {
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $this->adminEmail]);
        if ($existing instanceof User) {
            return false;
        }

        $editor = new User();
        $editor
            ->setEmail($this->adminEmail)
            ->setDisplayName('Floor editor')
            ->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR'])
            ->setPassword($this->passwordHasher->hashPassword($editor, $this->adminPassword));

        $this->entityManager->persist($editor);

        return true;
    }

    private function ensureCatalog(): bool
    {
        if (0 !== $this->entityManager->getRepository(Brand::class)->count([])) {
            return false;
        }

        $marlowe = $this->brand('Marlowe', 'marlowe', 'United Kingdom', 1964, 'Quiet grand tourers.');
        $vale = $this->brand('Vale Atelier', 'vale-atelier', 'Italy', 1958, 'Coachbuilt, numbered, kept.');
        $northline = $this->brand('Northline', 'northline', 'Sweden', 1972, 'Long light, cold roads.');

        $this->vehicle($marlowe, 'S2 Continental', 's2-continental', 2024, BodyStyle::Coupe, Drivetrain::RearWheel, 612, 24_800_000, VehicleStatus::Published, 'A long-hood coupe finished in night green, built for the late train home.');
        $this->vehicle($vale, 'Atelier 12', 'atelier-12', 2023, BodyStyle::Roadster, Drivetrain::RearWheel, 540, 31_000_000, VehicleStatus::Published, 'Twelve cars. Open roof. The body is aluminum, the wait is not.');
        $this->vehicle($northline, 'Arc', 'arc', 2025, BodyStyle::Wagon, Drivetrain::AllWheel, 408, 9_600_000, VehicleStatus::Published, 'A wagon with the stance of a rally car and room for a week away.');
        $this->vehicle($marlowe, 'Town', 'town', 2026, BodyStyle::Sedan, Drivetrain::RearWheel, 470, 18_500_000, VehicleStatus::Draft, 'Still in the studio. The grille is unsettled and the price is a pencil note.');

        $story = new Story();
        $story
            ->setTitle('Night delivery on the A9')
            ->setSlug('night-delivery-on-the-a9')
            ->setExcerpt('Moving a Continental after the showroom lights go down.')
            ->setBody('<p>The car left the floor at eleven. Rain on the A9, the cabin still smelling of wool and warm leather, and a single note on the passenger seat: deliver it before the owner wakes.</p>')
            ->setPublished(true);
        $this->entityManager->persist($story);

        return true;
    }

    private function brand(string $name, string $slug, string $country, int $year, string $tagline): Brand
    {
        $brand = new Brand();
        $brand
            ->setName($name)
            ->setSlug($slug)
            ->setCountry($country)
            ->setFoundedYear($year)
            ->setTagline($tagline);
        $this->entityManager->persist($brand);

        return $brand;
    }

    private function vehicle(
        Brand $brand,
        string $name,
        string $slug,
        int $year,
        BodyStyle $bodyStyle,
        Drivetrain $drivetrain,
        int $horsepower,
        int $price,
        VehicleStatus $status,
        string $summary,
    ): void {
        $vehicle = new Vehicle();
        $vehicle
            ->setBrand($brand)
            ->setName($name)
            ->setSlug($slug)
            ->setModelYear($year)
            ->setBodyStyle($bodyStyle)
            ->setDrivetrain($drivetrain)
            ->setHorsepower($horsepower)
            ->setPrice($price)
            ->setStatus($status)
            ->setSummary($summary);
        $this->entityManager->persist($vehicle);
    }
}
