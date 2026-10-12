<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceStationLaborPrice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceStationLaborPrice> */
final class MaintenanceStationLaborPriceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceStationLaborPrice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.station_labor_price.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.station_labor_price.plural', domain: 'messages'))
            ->setDefaultSort(['station' => 'ASC'])
            ->setSearchFields(['laborPriceUah']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('station', t('entity.station_labor_price.station', domain: 'messages'));
        yield AssociationField::new('procedure', t('entity.station_labor_price.procedure', domain: 'messages'))
            ->setHelp(t('entity.station_labor_price.procedure_help', domain: 'messages'));
        yield AssociationField::new('scheduleItem', t('entity.station_labor_price.schedule_item', domain: 'messages'))
            ->setHelp(t('entity.station_labor_price.schedule_item_help', domain: 'messages'));
        yield IntegerField::new('laborPriceUah', t('entity.station_labor_price.price', domain: 'messages'));
    }
}
