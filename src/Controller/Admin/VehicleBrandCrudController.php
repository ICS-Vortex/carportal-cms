<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleBrand;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<VehicleBrand> */
final class VehicleBrandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleBrand::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['name' => 'ASC'])->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('slug');
    }
}
