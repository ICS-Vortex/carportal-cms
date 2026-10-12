<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenancePlanRequest;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/** @extends AbstractCrudController<MaintenancePlanRequest> */
final class MaintenancePlanRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenancePlanRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user');
        yield AssociationField::new('vehicle');
        yield ChoiceField::new('status')->setChoices([
            'Open' => 'open',
            'Resolved' => 'resolved',
        ]);
        yield TextareaField::new('requestNote')->hideOnIndex();
    }
}
