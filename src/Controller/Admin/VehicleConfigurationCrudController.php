<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\VehicleConfiguration;
use App\Enum\FuelType;
use App\Enum\TransmissionType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

/** @extends AbstractCrudController<VehicleConfiguration> */
final class VehicleConfigurationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return VehicleConfiguration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(t('entity.configuration.singular', domain: 'messages'))
            ->setEntityLabelInPlural(t('entity.configuration.plural', domain: 'messages'))
            ->setSearchFields(['slug', 'engineLabel', 'transmissionLabel', 'fuelType']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('generation', t('entity.configuration.generation', domain: 'messages'));
        yield TextField::new('slug', t('entity.field.slug', domain: 'messages'));
        yield TextField::new('engineLabel', t('entity.configuration.engine', domain: 'messages'));
        yield ChoiceField::new('transmissionLabel', t('entity.configuration.transmission', domain: 'messages'))
            ->setChoices(TransmissionType::translatedChoices($this->translator));
        yield TextField::new('drivetrainLabel', t('entity.configuration.drivetrain', domain: 'messages'));
        yield ChoiceField::new('fuelType', t('entity.configuration.fuel', domain: 'messages'))
            ->setChoices(FuelType::translatedChoices($this->translator));
    }
}
