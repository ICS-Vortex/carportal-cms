<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceScheduleTemplate;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<MaintenanceScheduleTemplate> */
final class MaintenanceScheduleTemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceScheduleTemplate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['nameUk']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nameUk')->setLabel('Name');
        yield AssociationField::new('generation');
        yield AssociationField::new('configuration');
        yield BooleanField::new('deleted');
    }
}
