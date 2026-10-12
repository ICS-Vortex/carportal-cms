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
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<VehicleGeneration> */
final class VehicleGenerationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VehicleGeneration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.generation.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.generation.plural', domain: 'messages'))
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'slug']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('model', t('entity.generation.model', domain: 'messages'));
        yield TextField::new('name', t('entity.field.name', domain: 'messages'));
        yield TextField::new('slug', t('entity.field.slug', domain: 'messages'));
        yield IntegerField::new('yearFrom', t('entity.generation.year_from', domain: 'messages'));
        yield IntegerField::new('yearTo', t('entity.generation.year_to', domain: 'messages'));
        yield TextareaField::new('notesUk', t('entity.field.notes', domain: 'messages'))->hideOnIndex();
    }
}
