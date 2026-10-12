<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Brand;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Brand> */
final class BrandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Brand::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Brand')
            ->setEntityLabelInPlural('Brands')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'country', 'tagline']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex();
        yield TextField::new('country');
        yield IntegerField::new('foundedYear')->setLabel('Founded');
        yield TextField::new('tagline');
    }
}
