<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\EnumChoices;
use App\Entity\Vehicle;
use App\Enum\BodyStyle;
use App\Enum\Drivetrain;
use App\Enum\VehicleStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

/** @extends AbstractCrudController<Vehicle> */
final class VehicleCrudController extends AbstractCrudController
{
    use EnumChoices;

    public static function getEntityFqcn(): string
    {
        return Vehicle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Vehicle')
            ->setEntityLabelInPlural('Vehicles')
            ->setDefaultSort(['modelYear' => 'DESC'])
            ->setSearchFields(['name', 'slug', 'summary'])
            ->setPageTitle(Crud::PAGE_INDEX, 'The floor')
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices($this->enumChoices(VehicleStatus::class)))
            ->add(ChoiceFilter::new('bodyStyle')->setChoices($this->enumChoices(BodyStyle::class)));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addColumn(7);
        yield TextField::new('name');
        yield SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex();
        yield TextareaField::new('summary')->hideOnIndex();
        yield AssociationField::new('brand');
        yield IntegerField::new('modelYear')->setLabel('Year');

        yield FormField::addColumn(5);
        yield ChoiceField::new('status')
            ->setChoices($this->enumChoices(VehicleStatus::class))
            ->renderAsBadges([
                VehicleStatus::Draft->value => 'warning',
                VehicleStatus::Published->value => 'success',
            ]);
        yield ChoiceField::new('bodyStyle')->setChoices($this->enumChoices(BodyStyle::class))->setLabel('Body');
        yield ChoiceField::new('drivetrain')->setChoices($this->enumChoices(Drivetrain::class));
        yield IntegerField::new('horsepower')->setLabel('HP');
        yield MoneyField::new('price')->setCurrency('USD')->setStoredAsCents(true)->setNumDecimals(0);
        yield DateTimeField::new('publishedAt')->hideOnForm();
    }
}
