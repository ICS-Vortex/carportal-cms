<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Story;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/** @extends AbstractCrudController<Story> */
final class StoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Story::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Story')
            ->setEntityLabelInPlural('Stories')
            ->setDefaultSort(['title' => 'ASC'])
            ->setSearchFields(['title', 'excerpt']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');
        yield SlugField::new('slug')->setTargetFieldName('title')->hideOnIndex();
        yield TextareaField::new('excerpt')->hideOnIndex();
        yield TextEditorField::new('body')->hideOnIndex();
        yield BooleanField::new('published');
    }
}
