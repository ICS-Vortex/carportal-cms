<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceStationLaborPrice;
use App\Entity\User;
use App\Service\Admin\ServiceStationAccess;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceStationLaborPrice> */
final class MaintenanceStationLaborPriceCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ServiceStationAccess $stationAccess,
    ) {
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $user = $this->getUser();

        if ($user instanceof User) {
            $this->stationAccess->applyStationIdFilter($queryBuilder, $user, 'entity.station');
        }

        return $queryBuilder;
    }

    public static function getEntityFqcn(): string
    {
        return MaintenanceStationLaborPrice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.station_labor_price.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.station_labor_price.plural', domain: 'messages'))
            ->setPageTitle(Crud::PAGE_NEW, t('entity.station_labor_price.page_new', domain: 'messages'))
            ->setPageTitle(Crud::PAGE_EDIT, t('entity.station_labor_price.page_edit', domain: 'messages'))
            ->setDefaultSort(['station' => 'ASC'])
            ->setSearchFields(['laborPriceUah']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('station', t('entity.station_labor_price.station', domain: 'messages'));

        yield FormField::addFieldset(t('entity.station_labor_price.target_panel', domain: 'messages'))
            ->setHelp(t('entity.station_labor_price.target_panel_help', domain: 'messages'));

        yield AssociationField::new('procedure', t('entity.station_labor_price.procedure', domain: 'messages'))
            ->setHelp(t('entity.station_labor_price.procedure_help', domain: 'messages'))
            ->setQueryBuilder(static function ($repository) {
                return $repository->createQueryBuilder('procedure')
                    ->andWhere('procedure.deleted = false')
                    ->orderBy('procedure.titleUk', 'ASC');
            });

        yield AssociationField::new('scheduleItem', t('entity.station_labor_price.schedule_item', domain: 'messages'))
            ->setHelp(t('entity.station_labor_price.schedule_item_help', domain: 'messages'))
            ->setQueryBuilder(static function ($repository) {
                return $repository->createQueryBuilder('item')
                    ->addSelect('template', 'procedure')
                    ->join('item.template', 'template')
                    ->join('item.procedure', 'procedure')
                    ->andWhere('item.deleted = false')
                    ->orderBy('template.nameUk', 'ASC')
                    ->addOrderBy('procedure.titleUk', 'ASC');
            });

        yield IntegerField::new('laborPriceUah', t('entity.station_labor_price.price', domain: 'messages'));
    }
}
