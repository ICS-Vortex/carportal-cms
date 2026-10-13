<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ServiceStation;
use App\Entity\User;
use App\Portal\PortalMediaClient;
use App\Service\Admin\ServiceStationAccess;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<ServiceStation> */
final class ServiceStationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PortalMediaClient $portalMediaClient,
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if ($user instanceof User) {
            $this->stationAccess->applyStationIdFilter($queryBuilder, $user, 'entity.id');
        }

        return $queryBuilder;
    }

    public static function getEntityFqcn(): string
    {
        return ServiceStation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['slug', 'nameUk', 'cityUk']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('slug');
        yield TextField::new('nameUk', t('entity.service_station.name', domain: 'messages'));
        yield TextField::new('cityUk', t('entity.service_station.city', domain: 'messages'));
        yield TextField::new('addressUk', t('entity.service_station.address', domain: 'messages'));
        yield ImageField::new('imageUrl', t('entity.service_station.image', domain: 'messages'))
            ->hideOnForm();
        yield Field::new('imageUpload', t('entity.service_station.image_upload', domain: 'messages'))
            ->setFormType(FileType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/jpeg,image/png,image/webp'],
            ]);
        yield IntegerField::new('sortOrder');
        yield BooleanField::new('isActive');
        yield FormField::addFieldset(t('entity.service_station.appointment_panel', domain: 'messages'));
        yield IntegerField::new('appointmentSlotMinutes', t('entity.service_station.slot_minutes', domain: 'messages'))
            ->setHelp(t('entity.service_station.slot_minutes_help', domain: 'messages'));
        yield TimeField::new('appointmentDayStart', t('entity.service_station.day_start', domain: 'messages'));
        yield TimeField::new('appointmentDayEnd', t('entity.service_station.day_end', domain: 'messages'));
        yield IntegerField::new('appointmentSlotCapacity', t('entity.service_station.slot_capacity', domain: 'messages'))
            ->setHelp(t('entity.service_station.slot_capacity_help', domain: 'messages'));
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ServiceStation) {
            $this->applyUploadedImage($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof ServiceStation) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $existing = $entityManager->getRepository(ServiceStation::class)->find($entityInstance->getId());
        $previousUrl = $existing?->getImageUrl();

        $this->applyUploadedImage($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);

        $nextUrl = $entityInstance->getImageUrl();
        if ($previousUrl !== null && $previousUrl !== '' && $previousUrl !== $nextUrl) {
            $this->portalMediaClient->deleteManagedImage($previousUrl);
        }
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof ServiceStation) {
            $this->portalMediaClient->deleteManagedImage($entityInstance->getImageUrl());
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    private function applyUploadedImage(ServiceStation $station): void
    {
        $file = $this->resolveUploadedImageFile();

        if (!$file instanceof UploadedFile) {
            return;
        }

        $station->setImageUrl($this->portalMediaClient->uploadServiceStationImage($file));
    }

    private function resolveUploadedImageFile(): ?UploadedFile
    {
        $request = $this->getContext()?->getRequest();

        if ($request === null) {
            return null;
        }

        $bag = $request->files->get('ServiceStation');

        if (!is_array($bag)) {
            return null;
        }

        $file = $bag['imageUpload'] ?? null;

        return $file instanceof UploadedFile ? $file : null;
    }
}
