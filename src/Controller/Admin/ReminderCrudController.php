<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Reminder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Reminder> */
final class ReminderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reminder::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user');
        yield AssociationField::new('vehicle');
        yield AssociationField::new('procedure');
        yield DateField::new('dueDate');
        yield IntegerField::new('dueMileageKm');
        yield TextField::new('status');
    }
}
