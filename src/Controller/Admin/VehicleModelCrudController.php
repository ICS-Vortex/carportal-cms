<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleModel;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<VehicleModel> */
final class VehicleModelCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleModel::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.model.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.model.plural', domain: 'messages'))
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('brand', t('entity.model.brand', domain: 'messages'));
        yield TextField::new('name', t('entity.field.name', domain: 'messages'));
        yield TextField::new('slug', t('entity.field.slug', domain: 'messages'));
    }
}
