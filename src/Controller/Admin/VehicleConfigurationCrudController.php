<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<VehicleConfiguration> */
final class VehicleConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleConfiguration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['slug', 'engineLabel', 'transmissionLabel', 'fuelType']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('generation');
        yield TextField::new('slug');
        yield TextField::new('engineLabel');
        yield TextField::new('transmissionLabel');
        yield TextField::new('drivetrainLabel');
        yield TextField::new('fuelType');
    }
}
