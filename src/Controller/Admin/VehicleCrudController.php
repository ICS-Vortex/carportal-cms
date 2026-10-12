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
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['vin', 'licensePlate']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user');
        yield AssociationField::new('generation');
        yield AssociationField::new('configuration');
        yield IntegerField::new('year');
        yield TextField::new('vin');
        yield TextField::new('licensePlate');
        yield IntegerField::new('mileageKm')->setLabel('Mileage (km)');
        yield TextField::new('imageUrl')->hideOnIndex();
        yield BooleanField::new('deleted');
    }
}
