<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:bootstrap', description: 'Create the CMS admin when the users table has no admin.')]
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
        $existing = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $this->adminEmail]);
        if ($existing instanceof User) {
            $io->writeln('Admin already exists.');

            return Command::SUCCESS;
        }

        $admin = new User();
        $admin
            ->setEmail($this->adminEmail)
            ->setFullName('Floor editor')
            ->setRole('admin')
            ->setLocale('uk')
            ->setPassword($this->passwordHasher->hashPassword($admin, $this->adminPassword));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        $io->writeln(sprintf('Admin ready: %s', $this->adminEmail));

        return Command::SUCCESS;
    }
}
