<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenancePlanRequest;
use App\Entity\MaintenanceProcedure;
use App\Entity\Reminder;
use App\Entity\User;
use App\Entity\Vehicle;
use App\Entity\VehicleBrand;
use App\Admin\Notification\AdminNotificationHub;
use App\Admin\Notification\Provider\MaintenancePlanRequestNotificationProvider;
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
use function Symfony\Component\Translation\t;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private VehicleRepository $vehicles,
        private AdminNotificationHub $adminNotifications,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'users' => $this->entityManager->getRepository(User::class)->count([]),
            'vehicles' => $this->entityManager->getRepository(Vehicle::class)->count(['deleted' => false]),
            'brands' => $this->entityManager->getRepository(VehicleBrand::class)->count([]),
            'reminders' => $this->entityManager->getRepository(Reminder::class)->count([]),
            'procedures' => $this->entityManager->getRepository(MaintenanceProcedure::class)->count(['deleted' => false]),
            'notificationCount' => $this->adminNotifications->totalUnreadCount(),
            'notificationFeeds' => $this->adminNotifications->feeds(5),
            'recentVehicles' => $this->vehicles->recent(),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<span class="cars-mark">CAR PORTAL</span><span class="cars-mark-sub">Service</span>')
            ->setFaviconPath('favicon.svg')
            ->setDefaultColorScheme(ColorScheme::DARK)
            ->renderContentMaximized()
            ->setTheme(Theme::new()
                ->primaryColor('#0f766e', dark: '#3ee0c5')
                ->radius('0.7rem')
                ->spacing('md')
                ->grays('slate', dark: 'slate'));
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Instrument+Sans:wght@420;520;640&display=swap')
            ->addCssFile('styles/atelier.css')
            ->addJsFile('js/cars-toasts.js');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard(t('menu.overview'), 'fa-solid fa-gauge-high');
        yield MenuItem::section(t('menu.section.catalog'));
        yield MenuItem::linkTo(VehicleBrandCrudController::class, t('menu.brands'), 'fa-solid fa-flag');
        yield MenuItem::linkTo(VehicleModelCrudController::class, t('menu.models'), 'fa-solid fa-car');
        yield MenuItem::linkTo(VehicleGenerationCrudController::class, t('menu.generations'), 'fa-solid fa-layer-group');
        yield MenuItem::linkTo(VehicleConfigurationCrudController::class, t('menu.configurations'), 'fa-solid fa-gears');
        yield MenuItem::linkTo(VehicleCrudController::class, t('menu.vehicles'), 'fa-solid fa-car-side');
        yield MenuItem::section(t('menu.section.maintenance'));
        yield MenuItem::linkTo(MaintenanceProcedureCrudController::class, t('menu.procedures'), 'fa-solid fa-screwdriver-wrench');
        yield MenuItem::linkTo(MaintenanceScheduleTemplateCrudController::class, t('menu.templates'), 'fa-solid fa-clipboard-list');
        yield MenuItem::linkTo(ReminderCrudController::class, t('menu.reminders'), 'fa-solid fa-bell');
        $openPlanRequests = $this->adminNotifications->unreadCountForType(MaintenancePlanRequestNotificationProvider::TYPE);
        $planRequestsItem = MenuItem::linkTo(MaintenancePlanRequestCrudController::class, t('menu.plan_requests'), 'fa-solid fa-inbox');

        if ($openPlanRequests > 0) {
            $planRequestsItem = $planRequestsItem->setBadge($openPlanRequests, 'danger');
        }

        yield $planRequestsItem;
        yield MenuItem::section(t('menu.section.people'));
        yield MenuItem::linkTo(UserCrudController::class, t('menu.users'), 'fa-solid fa-user-tie');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $name = $user instanceof User ? $user->getFullName() : $user->getUserIdentifier();

        return parent::configureUserMenu($user)
            ->setName($name)
            ->addMenuItems([
                MenuItem::linkToRoute(t('account.title'), 'fa-solid fa-user-gear', 'admin_account_index'),
            ]);
    }
}
