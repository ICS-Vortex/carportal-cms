<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Brand;
use App\Entity\Publication;
use App\Entity\Story;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Enum\VehicleStatus;
use App\Repository\VehicleRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\ColorScheme;
use EasyCorp\Bundle\EasyAdminBundle\Config\Theme;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private VehicleRepository $vehicles,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'published' => $this->vehicles->countByStatus(VehicleStatus::Published),
            'drafts' => $this->vehicles->countByStatus(VehicleStatus::Draft),
            'brands' => $this->entityManager->getRepository(Brand::class)->count([]),
            'stories' => $this->entityManager->getRepository(Story::class)->count([]),
            'publications' => $this->entityManager->getRepository(Publication::class)->count([]),
            'recentVehicles' => $this->vehicles->recent(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span class="cars-mark">CARS</span><span class="cars-mark-sub">Atelier</span>')
            ->setFaviconPath('favicon.svg')
            ->setDefaultColorScheme(ColorScheme::DARK)
            ->renderContentMaximized()
            ->setTheme(Theme::new()
                ->primaryColor('#8a6a3b', dark: '#d4b483')
                ->radius('0.8rem')
                ->spacing('lg')
                ->grays('stone', dark: 'stone'));
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,520;9..144,640&family=Outfit:wght@360;480;560&display=swap')
            ->addCssFile('styles/atelier.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('The floor', 'fa fa-gauge-high');
        yield MenuItem::section('Catalog');
        yield MenuItem::linkToCrud('Vehicles', 'fa fa-car-side', Vehicle::class);
        yield MenuItem::linkToCrud('Brands', 'fa fa-flag', Brand::class);
        yield MenuItem::section('Editorial');
        yield MenuItem::linkToCrud('Stories', 'fa fa-feather', Story::class);
        yield MenuItem::section('Floor desk');
        yield MenuItem::linkToCrud('Publications', 'fa fa-tower-broadcast', Publication::class);
        yield MenuItem::linkToCrud('Editors', 'fa fa-user-tie', User::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $name = $user instanceof User ? $user->getDisplayName() : $user->getUserIdentifier();

        return parent::configureUserMenu($user)
            ->setName($name)
            ->addMenuItems([
                MenuItem::linkToLogout('Sign out', 'fa fa-arrow-right-from-bracket'),
            ]);
    }
}
