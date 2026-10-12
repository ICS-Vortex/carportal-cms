<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceScheduleItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceScheduleItem> */
final class MaintenanceScheduleItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceScheduleItem::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.schedule_item.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.schedule_item.plural', domain: 'messages'))
            ->setDefaultSort(['template' => 'ASC', 'procedure' => 'ASC'])
            ->setSearchFields(['notesUk']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('template', t('entity.schedule_item.template', domain: 'messages'));
        yield AssociationField::new('procedure', t('entity.schedule_item.procedure', domain: 'messages'));
        yield IntegerField::new('intervalKm', t('entity.schedule_item.interval_km', domain: 'messages'));
        yield IntegerField::new('intervalMonths', t('entity.schedule_item.interval_months', domain: 'messages'));
        yield IntegerField::new('laborPriceUah', t('entity.schedule_item.labor_price', domain: 'messages'))
            ->setHelp(t('entity.schedule_item.labor_price_help', domain: 'messages'));
        yield TextareaField::new('notesUk', t('entity.schedule_item.notes', domain: 'messages'))->hideOnIndex();
        yield BooleanField::new('deleted')->hideOnIndex();
    }
}
