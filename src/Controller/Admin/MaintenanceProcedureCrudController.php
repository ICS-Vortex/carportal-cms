<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceProcedure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<MaintenanceProcedure> */
final class MaintenanceProcedureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceProcedure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.procedure.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.procedure.plural', domain: 'messages'))
            ->setSearchFields(['code', 'titleUk', 'category']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('code');
        yield TextField::new('titleUk', t('entity.procedure.title', domain: 'messages'));
        yield TextField::new('category', t('entity.procedure.category', domain: 'messages'));
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
}
