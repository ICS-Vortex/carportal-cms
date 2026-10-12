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
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<Reminder> */
final class ReminderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reminder::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.reminder.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.reminder.plural', domain: 'messages'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('user', t('dashboard.table_owner', domain: 'messages'));
        yield AssociationField::new('vehicle', t('dashboard.table_vehicle', domain: 'messages'));
        yield AssociationField::new('procedure', t('entity.schedule_item.procedure', domain: 'messages'));
        yield DateField::new('dueDate', t('entity.reminder.due_date', domain: 'messages'));
        yield IntegerField::new('dueMileageKm', t('entity.reminder.due_mileage', domain: 'messages'));
        yield TextField::new('status', t('entity.plan_request.status', domain: 'messages'));
    }
}
