<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleBrand;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<VehicleBrand> */
final class VehicleBrandCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleBrand::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.brand.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.brand.plural', domain: 'messages'))
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', t('entity.field.name', domain: 'messages'));
        yield TextField::new('slug', t('entity.field.slug', domain: 'messages'));
    }
}
