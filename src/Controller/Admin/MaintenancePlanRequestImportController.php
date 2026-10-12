<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenancePlanImport;
use App\Entity\MaintenancePlanRequest;
use App\Entity\User;
use App\Form\MaintenancePlanImportUploadType;
use App\Maintenance\PlanImport\PlanImportFileStorage;
use App\Message\ProcessMaintenancePlanImportMessage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
#[IsGranted('ROLE_ADMIN')]
final class MaintenancePlanRequestImportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PlanImportFileStorage $fileStorage,
        private readonly MessageBusInterface $messageBus,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/admin/plan-request/{id}/import', name: 'admin_plan_request_import', methods: ['GET', 'POST'])]
    public function import(Request $request, string $id): Response
    {
        $planRequest = $this->entityManager->find(MaintenancePlanRequest::class, Uuid::fromString($id));
        if (!$planRequest instanceof MaintenancePlanRequest) {
            throw $this->createNotFoundException();
        }

        $vehicle = $planRequest->getVehicle();
        if ($vehicle === null) {
            throw $this->createNotFoundException('Автомобіль не привʼязано до запиту.');
        }

        $form = $this->createForm(MaintenancePlanImportUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploaded = $form->get('file')->getData();
            if (!$uploaded instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                throw new \RuntimeException('Invalid upload');
            }

            $storedPath = $this->fileStorage->store($uploaded);
            $import = new MaintenancePlanImport();
            $import->setPlanRequest($planRequest);
            $import->setVehicle($vehicle);
            $user = $this->getUser();
            if ($user instanceof User) {
                $import->setUploadedBy($user);
            }
            $import->setOriginalFilename($uploaded->getClientOriginalName());
            $import->setStoredPath($storedPath);
            $import->setMimeType($uploaded->getClientMimeType());
            $import->appendLog('Файл завантажено, поставлено в чергу RabbitMQ.');

            $this->entityManager->persist($import);
            $this->entityManager->flush();

            $this->messageBus->dispatch(new ProcessMaintenancePlanImportMessage((string) $import->getId()));

            $this->addFlash('success', 'Файл прийнято. Імпорт виконується у фоні (cms-worker).');

            return $this->redirectToRoute('admin_plan_request_import', ['id' => $id]);
        }

        $backUrl = $this->adminUrlGenerator
            ->setController(MaintenancePlanRequestCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($planRequest->getId())
            ->generateUrl();

        return $this->render('admin/plan_request_import.html.twig', [
            'planRequest' => $planRequest,
            'vehicle' => $vehicle,
            'form' => $form,
            'imports' => $planRequest->getImports(),
            'backUrl' => $backUrl,
        ]);
    }
}
