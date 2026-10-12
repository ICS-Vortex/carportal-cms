<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MaintenanceProcedure;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<MaintenanceProcedure> */
final class MaintenanceProcedureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceProcedure::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['code', 'titleUk', 'category']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('code');
        yield TextField::new('titleUk')->setLabel('Title');
        yield TextField::new('category');
        yield IntegerField::new('defaultIntervalKm')->setLabel('Interval km');
        yield IntegerField::new('defaultIntervalMonths')->setLabel('Interval months');
        yield IntegerField::new('laborPriceUah')->setLabel('Labor price (UAH)')->setHelp('Орієнтовна вартість робіт для кабінету власника.');
        yield TextareaField::new('descriptionUk')->hideOnIndex();
        yield BooleanField::new('deleted');
    }
}
