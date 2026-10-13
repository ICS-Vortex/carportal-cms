<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceProcedure;
use App\Entity\ServiceStation;
use App\Entity\User;
use App\Service\Admin\ServiceStationAccess;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\AsciiSlugger;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceProcedure> */
final class MaintenanceProcedureCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MaintenanceProcedure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.procedure.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.procedure.plural', domain: 'messages'))
            ->setPageTitle(Crud::PAGE_NEW, t('entity.procedure.page_new', domain: 'messages'))
            ->setPageTitle(Crud::PAGE_EDIT, t('entity.procedure.page_edit', domain: 'messages'))
            ->setSearchFields(['code', 'titleUk', 'category']);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if (!$user instanceof User || $this->stationAccess->isUnrestricted($user)) {
            return $queryBuilder;
        }

        $accessibleStations = $this->stationAccess->accessibleActiveStations($user);

        if ($accessibleStations === []) {
            return $queryBuilder
                ->andWhere('entity.createdBy = :procedureOwner')
                ->setParameter('procedureOwner', $user);
        }

        return $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->orX(
                    'entity.serviceStation IS NULL',
                    'entity.serviceStation IN (:procedureStations)',
                    'entity.createdBy = :procedureOwner'
                )
            )
            ->setParameter('procedureStations', $accessibleStations)
            ->setParameter('procedureOwner', $user);
    }

    public function configureFields(string $pageName): iterable
    {
        $user = $this->getUser();
        $unrestricted = $user instanceof User && $this->stationAccess->isUnrestricted($user);
        $accessibleStations = $user instanceof User ? $this->stationAccess->accessibleActiveStations($user) : [];
        $lockStationOnEdit = Crud::PAGE_EDIT === $pageName;

        $stationField = AssociationField::new('serviceStation', t('entity.procedure.station', domain: 'messages'))
            ->setHelp($unrestricted
                ? t('entity.procedure.station_help_global', domain: 'messages')
                : t('entity.procedure.station_help', domain: 'messages'))
            ->setRequired(!$unrestricted)
            ->setFormTypeOption('disabled', $lockStationOnEdit)
            ->hideOnIndex();

        if ($unrestricted) {
            $stationField->setQueryBuilder(static function (QueryBuilder $queryBuilder): QueryBuilder {
                return $queryBuilder
                    ->andWhere('entity.isActive = true')
                    ->orderBy('entity.sortOrder', 'ASC')
                    ->addOrderBy('entity.nameUk', 'ASC');
            });
        } else {
            $stationField->setChoices($accessibleStations);
        }

        yield $stationField;

        yield TextField::new('code')
            ->hideOnIndex()
            ->setHelp(t('entity.procedure.code_help', domain: 'messages'))
            ->setRequired(false);

        yield TextField::new('titleUk', t('entity.procedure.title', domain: 'messages'));
        yield TextField::new('category', t('entity.procedure.category', domain: 'messages'));

        yield AssociationField::new('serviceStation', t('entity.procedure.station', domain: 'messages'))
            ->onlyOnIndex();

        yield AssociationField::new('createdBy', t('entity.procedure.created_by', domain: 'messages'))
            ->hideOnForm();

        yield TextField::new('scheduleTemplatesLabel', t('entity.procedure.templates', domain: 'messages'))
            ->hideOnForm()
            ->setHelp(t('entity.procedure.templates_help', domain: 'messages'));

        yield IntegerField::new('defaultIntervalKm', t('entity.procedure.interval_km', domain: 'messages'))
            ->setHelp(t('entity.procedure.defaults_help', domain: 'messages'));
        yield IntegerField::new('defaultIntervalMonths', t('entity.procedure.interval_months', domain: 'messages'));
        yield IntegerField::new('laborPriceUah', t('entity.procedure.labor_price', domain: 'messages'))
            ->setHelp(t('entity.procedure.labor_price_help', domain: 'messages'));
        yield TextareaField::new('descriptionUk', t('entity.procedure.description', domain: 'messages'))->hideOnIndex();
        yield AssociationField::new('scheduleItems', t('entity.procedure.schedule_items', domain: 'messages'))
            ->onlyOnDetail();
        yield BooleanField::new('deleted');
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof MaintenanceProcedure) {
            $this->applyOwnershipAndCode($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof MaintenanceProcedure) {
            $this->ensureCode($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function applyOwnershipAndCode(MaintenanceProcedure $procedure): void
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $procedure->setCreatedBy($user);

            if (!$this->stationAccess->isUnrestricted($user)) {
                $accessibleStations = $this->stationAccess->accessibleActiveStations($user);
                if ($procedure->getServiceStation() === null && $accessibleStations !== []) {
                    $procedure->setServiceStation($accessibleStations[0]);
                }

                $station = $procedure->getServiceStation();
                if ($station !== null) {
                    $this->stationAccess->assertCanAccessStation($user, $station);
                }
            }
        }

        $this->ensureCode($procedure);
    }

    private function ensureCode(MaintenanceProcedure $procedure): void
    {
        if (trim($procedure->getCode()) !== '') {
            return;
        }

        $slugger = new AsciiSlugger('uk');
        $base = strtolower((string) $slugger->slug($procedure->getTitleUk())->toString());

        if ($base === '') {
            $base = 'procedure';
        }

        $station = $procedure->getServiceStation();
        $prefix = $station instanceof ServiceStation ? $station->getSlug().'-' : '';

        $procedure->setCode($prefix.$base);
    }
}
