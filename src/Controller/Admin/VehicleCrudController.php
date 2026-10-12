<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Vehicle;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<Vehicle> */
final class VehicleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vehicle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.vehicle.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.vehicle.plural', domain: 'messages'))
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['vin', 'licensePlate']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', t('dashboard.table_owner', domain: 'messages'));
        yield AssociationField::new('generation', t('entity.vehicle.generation', domain: 'messages'));
        yield AssociationField::new('configuration', t('entity.vehicle.configuration', domain: 'messages'));
        yield IntegerField::new('year', t('dashboard.table_year', domain: 'messages'));
        yield TextField::new('vin', t('entity.vehicle.vin', domain: 'messages'));
        yield TextField::new('licensePlate', t('entity.vehicle.plate', domain: 'messages'));
        yield IntegerField::new('mileageKm', t('dashboard.table_mileage', domain: 'messages'));
        yield TextField::new('imageUrl', t('entity.vehicle.image', domain: 'messages'))->hideOnIndex();
        yield BooleanField::new('deleted', t('entity.field.deleted', domain: 'messages'));
    }
}
