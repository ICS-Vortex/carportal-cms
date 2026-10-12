<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleModel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<VehicleModel> */
final class VehicleModelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleModel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['name' => 'ASC'])->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('brand');
        yield TextField::new('name');
        yield TextField::new('slug');
    }
}
