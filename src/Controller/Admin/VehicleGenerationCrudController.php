<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleGeneration;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<VehicleGeneration> */
final class VehicleGenerationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleGeneration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['name' => 'ASC'])->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('model');
        yield TextField::new('name');
        yield TextField::new('slug');
        yield IntegerField::new('yearFrom');
        yield IntegerField::new('yearTo');
        yield TextareaField::new('notesUk')->hideOnIndex();
    }
}
